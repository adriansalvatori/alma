<?php

namespace App\Domains\Ai\Hooks;

use App\Domains\Ai\Models\ToolCall;
use Throwable;
use WP_Error;

/**
 * Records every ability call (who ran what, the outcome, how long it took) in
 * alma_ai_tool_calls, shown in Settings → AI Access.
 *
 * It hooks core's own extension points rather than wrapping the abilities, so calls made through
 * any channel (MCP, the REST run endpoint, PHP) are covered, including core's and other plugins'
 * abilities. Each outcome has its own hook: invalid input, a denied permission check, and the
 * result of the execute callback.
 *
 * The log must never cost the agent its call: every write is fail-soft, and the filters return
 * their value untouched.
 */
class ToolAuditLog
{
    private const SENSITIVE_KEY = '/pass(word)?|secret|token|api[_-]?key|authorization|cookie|base64|^data$/i';

    /**
     * Start times of the calls in flight, by ability name. A stack per name, because the MCP
     * adapter's execute-ability runs the target ability inside its own call.
     *
     * @var array<string, list<float>>
     */
    private array $started = [];

    public function register(): void
    {
        add_action('wp_ability_invoked', [$this, 'started'], 10, 1);
        add_filter('wp_ability_validate_input', [$this, 'recordInvalid'], 999, 3);
        add_filter('wp_ability_permission_result', [$this, 'recordDenied'], 999, 3);
        add_filter('wp_ability_execute_result', [$this, 'recordResult'], 999, 3);
    }

    public function started(string $ability): void
    {
        $this->started[$ability][] = microtime(true);
    }

    public function recordInvalid(mixed $valid, mixed $input, string $ability): mixed
    {
        if ($valid instanceof WP_Error || $valid === false) {
            $this->write($ability, ToolCall::INVALID, $input, $valid instanceof WP_Error ? $valid->get_error_message() : null);
        }

        return $valid;
    }

    public function recordDenied(mixed $permission, string $ability, mixed $input): mixed
    {
        if ($permission !== true) {
            $this->write($ability, ToolCall::DENIED, $input, $permission instanceof WP_Error ? $permission->get_error_message() : null);
        }

        return $permission;
    }

    public function recordResult(mixed $result, string $ability, mixed $input): mixed
    {
        $outcome = match (true) {
            $result instanceof WP_Error => ToolCall::FAILED,
            is_array($result) && ($result['status'] ?? null) === 'confirmation_required' => ToolCall::AWAITING_CONFIRMATION,
            default => ToolCall::SUCCEEDED,
        };

        $this->write($ability, $outcome, $input, $result instanceof WP_Error ? $result->get_error_message() : null);

        return $result;
    }

    /**
     * A JSON summary of the input that is safe to keep: secrets and file contents redacted, long
     * strings cut, the whole thing capped.
     */
    public static function summarize(mixed $input): ?string
    {
        if ($input === null || $input === [] || $input === '') {
            return null;
        }

        $json = json_encode(self::scrub($input), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        return mb_strimwidth((string) $json, 0, 2000, '…');
    }

    private static function scrub(mixed $value, int $depth = 0, int|string|null $key = null): mixed
    {
        if (is_string($key) && preg_match(self::SENSITIVE_KEY, $key)) {
            return '[redacted]';
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            if ($depth >= 4) {
                return '[…]';
            }

            $clean = [];

            foreach ($value as $k => $v) {
                $clean[$k] = self::scrub($v, $depth + 1, $k);
            }

            return $clean;
        }

        if (is_string($value) && mb_strlen($value) > 200) {
            return mb_substr($value, 0, 120).'… ('.mb_strlen($value).' chars)';
        }

        return $value;
    }

    private function write(string $ability, string $outcome, mixed $input, ?string $error = null): void
    {
        $started = isset($this->started[$ability]) ? array_pop($this->started[$ability]) : null;

        if (in_array($ability, (array) config('ai.audit.ignore', []), true)) {
            return;
        }

        try {
            ToolCall::query()->create([
                'user_id' => get_current_user_id() ?: null,
                'ability' => mb_substr($ability, 0, 191),
                'outcome' => $outcome,
                'input_summary' => self::summarize($input),
                'error' => $error !== null ? mb_substr($error, 0, 1000) : null,
                'duration_ms' => $started !== null ? (int) round((microtime(true) - $started) * 1000) : null,
            ]);

            $this->maybePrune();
        } catch (Throwable $e) {
            logger()->warning("AI tool audit log write failed: {$e->getMessage()}");
        }
    }

    /**
     * Prune old rows on roughly one write in prune_odds, so the table stays bounded without a
     * cron job (shared hosting often has an unreliable one).
     */
    private function maybePrune(): void
    {
        $odds = max(1, (int) config('ai.audit.prune_odds', 50));

        if (random_int(1, $odds) !== 1) {
            return;
        }

        ToolCall::query()
            ->where('created_at', '<', now()->subDays((int) config('ai.audit.retention_days', 90)))
            ->delete();
    }
}

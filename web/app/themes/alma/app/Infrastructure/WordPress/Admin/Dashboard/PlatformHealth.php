<?php

namespace App\Infrastructure\WordPress\Admin\Dashboard;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthChecker;
use App\Infrastructure\Observability\IntegrationLog\IntegrationCall;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * What the dashboard's "Platform & Infrastructure Health" widget shows: the environment, every
 * health check with its evidence, and each third party the site has called lately, judged from
 * the integration log.
 *
 * Cached for a minute. The dashboard is the first screen after logging in, so it renders often,
 * and the checks probe the database and read the cron table. Cached as plain arrays.
 */
class PlatformHealth
{
    public const CACHE_KEY = 'alma_admin_dashboard_health';

    private const TTL = 60;

    /** How far back the integration rows look, and how many calls judge each host. */
    private const INTEGRATION_DAYS = 7;

    private const SAMPLE = 50;

    private const MAX_HOSTS = 8;

    public function __construct(private readonly HealthChecker $checker) {}

    /**
     * @return array{platform: array{env: string, release: string, php: string, memory_limit: string}, checks: list<array<string, mixed>>, integrations: list<array<string, mixed>>, overall: string}
     */
    public function snapshot(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, fn () => $this->gather());
    }

    /**
     * The overall status from the last snapshot still cached, or null when there is none. Never
     * runs a check: the admin footer reads it on every screen.
     */
    public static function cachedOverall(): ?string
    {
        try {
            $snapshot = Cache::get(self::CACHE_KEY);
        } catch (Throwable) {
            return null;
        }

        return is_array($snapshot) && is_string($snapshot['overall'] ?? null) ? $snapshot['overall'] : null;
    }

    /**
     * @return array{platform: array{env: string, release: string, php: string, memory_limit: string}, checks: list<array<string, mixed>>, integrations: list<array<string, mixed>>, overall: string}
     */
    private function gather(): array
    {
        $labels = [];

        foreach ($this->checker->checks() as $check) {
            $labels[$check->name()] = $check->label();
        }

        $results = $this->checker->run();
        $checks = [];

        foreach ($results as $name => $result) {
            $checks[] = self::row(
                $labels[$name] ?? ucfirst((string) $name),
                $result->summary,
                $result->status,
                $result->details,
            );
        }

        return [
            'platform' => [
                'env' => ucfirst(wp_get_environment_type()),
                'release' => (string) config('alma.version', 'dev'),
                'php' => PHP_VERSION,
                'memory_limit' => (string) (ini_get('memory_limit') ?: '—'),
            ],
            'checks' => $checks,
            'integrations' => $this->integrations(),
            'overall' => HealthChecker::overall($results),
        ];
    }

    /**
     * One row per host the site called in the last week, busiest first.
     *
     * Down after three failures in a row; degraded after one, or when a fifth of the recent calls
     * failed. The evidence (the same figures) sits behind the row's "Details".
     *
     * @return list<array<string, mixed>>
     */
    private function integrations(): array
    {
        try {
            $since = now()->subDays(self::INTEGRATION_DAYS);

            $hosts = IntegrationCall::query()
                ->where('created_at', '>=', $since)
                ->selectRaw('host, count(*) as calls')
                ->groupBy('host')
                ->orderByDesc('calls')
                ->limit(self::MAX_HOSTS)
                ->pluck('calls', 'host');
        } catch (Throwable) {
            return [];
        }

        $rows = [];

        foreach ($hosts as $host => $calls) {
            $recent = IntegrationCall::query()
                ->where('host', $host)
                ->orderByDesc('id')
                ->limit(self::SAMPLE)
                ->get();

            $consecutive = 0;

            foreach ($recent as $call) {
                if ($call->succeeded()) {
                    break;
                }

                $consecutive++;
            }

            $failures = $recent->reject(fn (IntegrationCall $call) => $call->succeeded())->count();
            $rate = $recent->isNotEmpty() ? $failures / $recent->count() : 0.0;
            $lastError = $recent->first(fn (IntegrationCall $call) => ! $call->succeeded());

            $status = match (true) {
                $consecutive >= 3 => CheckResult::CRITICAL,
                $consecutive >= 1 || $rate >= 0.2 => CheckResult::WARNING,
                default => CheckResult::OK,
            };

            $rows[] = self::row(
                (string) $host,
                sprintf(_n('%1$d call in %2$d days', '%1$d calls in %2$d days', (int) $calls, 'alma'), (int) $calls, self::INTEGRATION_DAYS),
                $status,
                [
                    __('Consecutive failures', 'alma') => $consecutive,
                    __('Failure rate', 'alma') => round($rate * 100).'%',
                    __('Sample size', 'alma') => sprintf(_n('%d call', '%d calls', $recent->count(), 'alma'), $recent->count()),
                    __('Last call', 'alma') => $recent->first()?->created_at?->diffForHumans() ?? '—',
                    __('Last error', 'alma') => $lastError === null ? '—' : ($lastError->error ?: 'HTTP '.$lastError->status),
                ],
            );
        }

        return $rows;
    }

    /**
     * One row in the widget's shape: the indicator and badge classes, the label, and evidence.
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private static function row(string $title, string $description, string $status, array $details): array
    {
        return [
            'title' => $title,
            'desc' => $description,
            'status' => $status,
            'label' => match ($status) {
                CheckResult::OK => __('Healthy', 'alma'),
                CheckResult::WARNING => __('Degraded', 'alma'),
                default => __('Down', 'alma'),
            },
            'style' => match ($status) {
                CheckResult::OK => ['indicator' => 'alma-dash-indicator-green', 'badge' => 'alma-badge-emerald'],
                CheckResult::WARNING => ['indicator' => 'alma-dash-indicator-amber', 'badge' => 'alma-badge-amber'],
                default => ['indicator' => 'alma-dash-indicator-red', 'badge' => 'alma-badge-rose'],
            },
            'details' => array_map(
                fn ($value) => is_scalar($value) || $value === null ? (string) $value : (string) wp_json_encode($value),
                $details,
            ),
        ];
    }
}

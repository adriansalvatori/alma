<?php

namespace App\Domains\Ai\Abilities;

use App\Domains\Ai\Safety\ConfirmationToken;
use WP_Error;

/**
 * One thing Claude can do on the site, registered with the WordPress Abilities API.
 *
 * Subclasses hold behaviour only. Registration (categories, meta, MCP exposure) lives in
 * definition() so every ability is exposed the same way, and config/ai.php decides which ones
 * exist.
 *
 * Input always arrives as an array: the schema defaults to an empty object, so an ability called
 * with no input still validates.
 *
 * A destructive ability runs in two steps. The first call returns a preview and a confirmation
 * token; only a second call carrying that token (and the same input) does the work. The token
 * is stateless, so it survives a client that opens a new MCP session between the two calls.
 */
abstract class Ability
{
    /**
     * The registered name, namespaced: "alma/list-pages".
     */
    abstract public function name(): string;

    abstract public function label(): string;

    /**
     * What the ability does and when to use it. Agents choose tools by reading this.
     */
    abstract public function description(): string;

    abstract public function category(): string;

    /**
     * Whether the current user may run it. Each ability checks its own capability.
     *
     * @param  array<string, mixed>  $input
     */
    abstract public function permission(array $input): bool|WP_Error;

    /**
     * @param  array<string, mixed>  $input
     */
    abstract public function execute(array $input): mixed;

    /**
     * JSON Schema properties for the input.
     *
     * @return array<string, mixed>
     */
    protected function inputProperties(): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    protected function requiredInput(): array
    {
        return [];
    }

    /**
     * JSON Schema for a successful result. Empty means unvalidated.
     *
     * @return array<string, mixed>
     */
    public function outputSchema(): array
    {
        return [];
    }

    public function readOnly(): bool
    {
        return false;
    }

    public function destructive(): bool
    {
        return false;
    }

    public function idempotent(): bool
    {
        return false;
    }

    /**
     * Whether the default MCP server lists it. Every ability is still reachable over REST by a
     * user with the capability.
     */
    public function mcpPublic(): bool
    {
        return true;
    }

    /**
     * Whether the ability can run on this install at all (its module is loaded, for example).
     * Unavailable abilities are not registered.
     */
    public function available(): bool
    {
        return true;
    }

    /**
     * What a destructive ability would change, shown before the confirmation step.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>|WP_Error
     */
    public function preview(array $input): array|WP_Error
    {
        return ['input' => $input];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputSchema(): array
    {
        $properties = $this->inputProperties();

        if ($this->destructive()) {
            $properties['confirmation_token'] = [
                'type' => 'string',
                'description' => 'Leave empty on the first call: it returns a preview and a token. Repeat the call with the same input and that token to carry it out.',
            ];
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties === [] ? (object) [] : $properties,
            'additionalProperties' => false,
            'default' => [],
        ];

        if (($required = $this->requiredInput()) !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * The arguments for wp_register_ability().
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $output = $this->outputSchema();

        if ($this->destructive() && $output !== []) {
            $output = ['oneOf' => [self::confirmationSchema(), $output]];
        }

        return array_filter([
            'label' => $this->label(),
            'description' => $this->description(),
            'category' => $this->category(),
            'input_schema' => $this->inputSchema(),
            'output_schema' => $output,
            'execute_callback' => fn (mixed $input = null) => $this->run($input),
            'permission_callback' => fn (mixed $input = null) => $this->permission(self::normalize($input)),
            'meta' => [
                'annotations' => [
                    'readonly' => $this->readOnly(),
                    'destructive' => $this->destructive(),
                    'idempotent' => $this->idempotent(),
                ],
                'public' => $this->mcpPublic(),
                'show_in_rest' => true,
                'mcp' => ['public' => $this->mcpPublic(), 'type' => 'tool'],
            ],
        ], fn ($value) => $value !== []);
    }

    /**
     * The execute callback: the confirmation step for destructive abilities, then execute().
     */
    public function run(mixed $input = null): mixed
    {
        $input = self::normalize($input);

        if (! $this->destructive()) {
            return $this->execute($input);
        }

        $tokens = app(ConfirmationToken::class);
        $token = is_string($input['confirmation_token'] ?? null) ? $input['confirmation_token'] : '';
        unset($input['confirmation_token']);

        if ($token === '') {
            $preview = $this->preview($input);

            if ($preview instanceof WP_Error) {
                return $preview;
            }

            return [
                'status' => 'confirmation_required',
                'preview' => $preview,
                'confirmation_token' => $tokens->issue($this->name(), get_current_user_id(), $input),
                'expires_in' => $tokens->ttl(),
            ];
        }

        if (! $tokens->verify($token, $this->name(), get_current_user_id(), $input)) {
            return new WP_Error(
                'alma_ai_confirmation_invalid',
                __('The confirmation token is missing, expired, or was issued for different input. Call again without a token to get a fresh preview.', 'alma'),
            );
        }

        return $this->execute($input);
    }

    /**
     * @return array<string, mixed>
     */
    public static function normalize(mixed $input): array
    {
        if (is_object($input)) {
            $input = get_object_vars($input);
        }

        return is_array($input) ? $input : [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function confirmationSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => ['type' => 'string', 'enum' => ['confirmation_required']],
                'preview' => ['type' => 'object'],
                'confirmation_token' => ['type' => 'string'],
                'expires_in' => ['type' => 'integer'],
            ],
            'required' => ['status', 'preview', 'confirmation_token'],
        ];
    }
}

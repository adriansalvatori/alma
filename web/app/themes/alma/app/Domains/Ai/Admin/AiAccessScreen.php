<?php

namespace App\Domains\Ai\Admin;

use App\Domains\Ai\Deploy\ActivateMcpAdapter;
use App\Domains\Ai\Models\ToolCall;
use App\Domains\Ai\Provisioning\AgentProvisioner;
use RuntimeException;
use Throwable;

/**
 * Settings → AI Access: everything needed to connect Claude, for an administrator without a shell.
 *
 * Status of the prerequisites, the agent user and its capability groups, named credentials
 * (issued here, the plaintext shown exactly once, individually revocable), the MCP endpoint and
 * ready-to-paste client configs, and the most recent tool calls.
 *
 * Every action is a nonce-checked POST to admin-post.php behind manage_options.
 */
class AiAccessScreen
{
    public const PAGE = 'alma-ai-access';

    public const CAPABILITY = 'manage_options';

    /** Holds a just-issued password for the admin who issued it, until the next page view. */
    private const ISSUED = 'alma_ai_issued_';

    public function __construct(
        private readonly AgentProvisioner $agent,
        private readonly AuthorizationProbe $probe,
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_alma_ai_issue', [$this, 'issue']);
        add_action('admin_post_alma_ai_revoke', [$this, 'revoke']);
        add_action('admin_post_alma_ai_reconcile', [$this, 'reconcile']);
        add_action('admin_post_alma_ai_probe', [$this, 'runProbe']);
    }

    public static function url(array $args = []): string
    {
        return add_query_arg(['page' => self::PAGE] + $args, admin_url('options-general.php'));
    }

    public function menu(): void
    {
        add_options_page(__('AI Access', 'alma'), __('AI Access', 'alma'), self::CAPABILITY, self::PAGE, [$this, 'render']);
    }

    public function render(): void
    {
        $this->authorize();

        echo view('ai::admin.access', $this->data())->render();
    }

    /**
     * Everything the screen shows, gathered in one place so it can be tested without rendering.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $user = $this->agent->user();
        $issued = get_transient(self::ISSUED.get_current_user_id());
        delete_transient(self::ISSUED.get_current_user_id());

        $endpoint = rest_url('mcp/mcp-adapter-default-server');

        return [
            'status' => $this->status(),
            'login' => $this->agent->login(),
            'user' => $user,
            'grants' => array_map(fn (array $grant) => $grant + [
                'actual' => $user !== null && array_reduce($grant['capabilities'], fn (bool $all, string $cap) => $all && user_can($user, $cap), true),
            ], $this->agent->grants()),
            'credentials' => $user !== null ? $this->agent->credentials() : [],
            'issued' => is_array($issued) ? $issued : null,
            'endpoint' => $endpoint,
            'desktopConfig' => $this->clientConfig($endpoint, is_array($issued) ? $issued['password'] : null),
            'calls' => $this->recentCalls(),
            'notice' => isset($_GET['notice']) ? sanitize_key((string) $_GET['notice']) : null,
        ];
    }

    /**
     * @return list<array{label: string, ok: bool|null, detail: string}>
     */
    public function status(): array
    {
        $https = is_ssl() || str_starts_with(home_url(), 'https://');
        $probe = $this->probe->last();
        $adapterActive = function_exists('is_plugin_active') ? is_plugin_active(ActivateMcpAdapter::PLUGIN) : null;

        return [
            [
                'label' => __('HTTPS', 'alma'),
                'ok' => $https,
                'detail' => $https ? __('Credentials travel encrypted.', 'alma') : __('Application Passwords only work over HTTPS outside local development.', 'alma'),
            ],
            [
                'label' => __('Application Passwords', 'alma'),
                'ok' => $passwords = function_exists('wp_is_application_passwords_available') && wp_is_application_passwords_available(),
                'detail' => match (true) {
                    $passwords => __('Available.', 'alma'),
                    ! $https => __('Off: WordPress only offers them over HTTPS (or with WP_ENVIRONMENT_TYPE=local).', 'alma'),
                    default => __('Off: a security plugin or filter disabled them (Wordfence does by default).', 'alma'),
                },
            ],
            [
                'label' => __('MCP adapter plugin', 'alma'),
                'ok' => $adapterActive === null ? null : ($adapterActive && class_exists('WP\MCP\Core\McpAdapter')),
                'detail' => match (true) {
                    $adapterActive && class_exists('WP\MCP\Core\McpAdapter') => __('Active; serves the MCP endpoint.', 'alma'),
                    (bool) $adapterActive => __('Active but its classes did not load: define WP_MCP_AUTOLOAD as false in config/application.php (Composer autoloads it).', 'alma'),
                    ActivateMcpAdapter::loadable() => __('Inactive. The next deploy activates it, or activate it under Plugins.', 'alma'),
                    default => __('Inactive, and it cannot load yet: define WP_MCP_AUTOLOAD as false in config/application.php, then deploy.', 'alma'),
                },
            ],
            [
                'label' => __('Authorization header', 'alma'),
                'ok' => $probe === null || $probe['status'] === 'unknown' ? null : $probe['status'] === 'ok',
                'detail' => $probe === null ? __('Not checked yet. Run the check below.', 'alma') : $probe['detail'],
            ],
        ];
    }

    public function issue(): void
    {
        $this->authorize();
        check_admin_referer('alma_ai_issue');

        try {
            $this->agent->ensure();
            $issued = $this->agent->issue((string) wp_unslash($_POST['label'] ?? ''));
            set_transient(self::ISSUED.get_current_user_id(), $issued + ['login' => $this->agent->login()], 5 * MINUTE_IN_SECONDS);
            $notice = 'issued';
        } catch (RuntimeException $e) {
            $notice = 'issue_failed';
        }

        $this->back($notice);
    }

    public function revoke(): void
    {
        $this->authorize();
        check_admin_referer('alma_ai_revoke');

        $this->back($this->agent->revoke(sanitize_text_field((string) wp_unslash($_POST['uuid'] ?? ''))) ? 'revoked' : 'revoke_failed');
    }

    public function reconcile(): void
    {
        $this->authorize();
        check_admin_referer('alma_ai_reconcile');

        try {
            $this->agent->ensure();
            $notice = 'reconciled';
        } catch (Throwable) {
            $notice = 'reconcile_failed';
        }

        $this->back($notice);
    }

    public function runProbe(): void
    {
        $this->authorize();
        check_admin_referer('alma_ai_probe');

        $this->probe->run();
        $this->back('probed');
    }

    /**
     * The Claude Desktop / Claude Code server entry, through the STDIO proxy that turns the HTTP
     * endpoint into a local MCP server.
     */
    public function clientConfig(string $endpoint, ?string $password = null): string
    {
        $name = sanitize_key((string) parse_url(home_url(), PHP_URL_HOST)) ?: 'wordpress';

        return (string) wp_json_encode([
            'mcpServers' => [
                $name => [
                    'command' => 'npx',
                    'args' => ['-y', '@automattic/mcp-wordpress-remote@latest'],
                    'env' => [
                        'WP_API_URL' => $endpoint,
                        'WP_API_USERNAME' => $this->agent->login(),
                        'WP_API_PASSWORD' => $password ?? '<application password>',
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentCalls(): array
    {
        try {
            return ToolCall::query()->latest('id')->limit(50)->get()->map(fn (ToolCall $call) => [
                'when' => $call->created_at?->toDateTimeString(),
                'user' => $call->user_id ? (get_userdata($call->user_id)->user_login ?? '#'.$call->user_id) : '—',
                'ability' => $call->ability,
                'outcome' => $call->outcome,
                'duration_ms' => $call->duration_ms,
                'error' => $call->error,
            ])->all();
        } catch (Throwable) {
            // The table arrives with the next deploy; the screen still works before it does.
            return [];
        }
    }

    private function authorize(): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You do not have permission to manage AI access.', 'alma'), '', ['response' => 403]);
        }
    }

    private function back(string $notice): never
    {
        wp_safe_redirect(self::url(['notice' => $notice]));
        exit;
    }
}

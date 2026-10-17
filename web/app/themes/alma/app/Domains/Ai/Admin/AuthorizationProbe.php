<?php

namespace App\Domains\Ai\Admin;

use WP_REST_Request;

/**
 * Finds out whether an Authorization header survives the trip to PHP on this host.
 *
 * Application Passwords (and so every MCP client) authenticate with that header, and many shared
 * hosts running PHP as CGI/FastCGI drop it unless .htaccess passes it through. When it is dropped
 * the agent gets a bare 401 that looks exactly like a wrong password. The probe sends a request to
 * itself carrying a harmless Bearer header (core's Application Password check only reads Basic,
 * so it cannot trip a failed login) and asks this endpoint whether PHP saw it.
 */
class AuthorizationProbe
{
    public const ROUTE_NAMESPACE = 'alma/v1';

    public const ROUTE = '/ai/authorization-probe';

    public const RESULT = 'alma_ai_authorization_probe';

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'route']);
    }

    public function route(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE, [
            'methods' => 'GET',
            'callback' => [$this, 'respond'],
            // Says only whether a header arrived; nothing about the site or its users.
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * @return array{received: bool}
     */
    public function respond(?WP_REST_Request $request = null): array
    {
        return ['received' => self::headerReceived($_SERVER)];
    }

    /**
     * WordPress reads Basic credentials from the same two server variables
     * (wp_populate_basic_auth_from_authorization_header).
     *
     * @param  array<string, mixed>  $server
     */
    public static function headerReceived(array $server): bool
    {
        return ! empty($server['HTTP_AUTHORIZATION']) || ! empty($server['REDIRECT_HTTP_AUTHORIZATION']);
    }

    /**
     * Run the probe and remember the result for a day.
     *
     * @return array{status: string, checked_at: int, detail: string}
     */
    public function run(): array
    {
        $response = wp_remote_get(rest_url(self::ROUTE_NAMESPACE.self::ROUTE), [
            'headers' => ['Authorization' => 'Bearer alma-authorization-probe'],
            'timeout' => 10,
            // A loopback request; local certificates are often self-signed (Site Health does the same).
            'sslverify' => apply_filters('https_local_ssl_verify', false),
        ]);

        if (is_wp_error($response)) {
            $result = ['status' => 'unknown', 'detail' => $response->get_error_message()];
        } else {
            $body = json_decode((string) wp_remote_retrieve_body($response), true);
            $received = is_array($body) ? ($body['received'] ?? null) : null;

            $result = match ($received) {
                true => ['status' => 'ok', 'detail' => 'The Authorization header reaches PHP.'],
                false => ['status' => 'stripped', 'detail' => 'The host drops the Authorization header. Add the pass-through rule to web/.htaccess (see docs/ai.md).'],
                default => ['status' => 'unknown', 'detail' => 'Unexpected response (HTTP '.wp_remote_retrieve_response_code($response).').'],
            };
        }

        $result['checked_at'] = time();
        set_transient(self::RESULT, $result, DAY_IN_SECONDS);

        return $result;
    }

    /**
     * @return array{status: string, checked_at: int, detail: string}|null
     */
    public function last(): ?array
    {
        $result = get_transient(self::RESULT);

        return is_array($result) ? $result : null;
    }
}

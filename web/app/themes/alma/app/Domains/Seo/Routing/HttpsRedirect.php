<?php

namespace App\Domains\Seo\Routing;

/**
 * Send plain-HTTP page requests to HTTPS, when the site itself is configured as https.
 *
 * web/.htaccess has the same rule commented out; enable it there when the host terminates TLS on
 * the same machine, because the web server is cheaper than booting WordPress. This hook is the
 * fallback that works everywhere, including behind a proxy or CDN that sets X-Forwarded-Proto
 * (Bedrock already maps that header to $_SERVER['HTTPS']).
 *
 * Skipped entirely when WP_HOME is http (local development).
 */
class HttpsRedirect
{
    public function register(): void
    {
        add_action('template_redirect', [$this, 'handle'], 1);
    }

    public function handle(): void
    {
        $target = $this->target($_SERVER);

        if ($target !== null && wp_safe_redirect($target, 301, 'Alma')) {
            $this->terminate();
        }
    }

    /**
     * The https URL to redirect to, or null when the request is fine as it is.
     *
     * @param  array<string, mixed>  $server
     */
    public function target(array $server): ?string
    {
        if (parse_url(home_url(), PHP_URL_SCHEME) !== 'https' || $this->isSecure($server)) {
            return null;
        }

        $host = parse_url(home_url(), PHP_URL_HOST);
        $uri = '/'.ltrim((string) ($server['REQUEST_URI'] ?? '/'), '/');

        return "https://{$host}{$uri}";
    }

    /**
     * @param  array<string, mixed>  $server
     */
    protected function isSecure(array $server): bool
    {
        if (! empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off') {
            return true;
        }

        if (strtolower((string) ($server['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
            return true;
        }

        return (string) ($server['SERVER_PORT'] ?? '') === '443';
    }

    protected function terminate(): never
    {
        exit;
    }
}

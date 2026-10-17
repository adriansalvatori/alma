<?php

namespace App\Domains\Seo\Routing;

use Illuminate\Routing\Route;
use Throwable;

/**
 * Turn "unknown path, empty query" into a real 404.
 *
 * With verbose page rewrite rules (any permalink structure with a prefix, or a page and a post
 * sharing a base), WordPress can fail to match an unknown path to any rule and leave the query
 * empty. An empty query is the front page, so every mistyped URL served the homepage with a 200:
 * soft 404s that search engines index as duplicates. Remote Leverage shipped that for months.
 *
 * Runs on parse_request after everything else. Acorn routes never reach this point (Acorn
 * answers them on parse_request at priority 10 and exits), but their URIs are excluded anyway in
 * case a route is registered without being matched.
 */
class MissingPathNotFound
{
    /**
     * Paths WordPress serves without a rewrite rule.
     */
    private const IGNORED_PREFIXES = ['wp-admin', 'wp-json', 'wp', 'wp-login.php', 'wp-cron.php', 'xmlrpc.php', 'robots.txt', 'favicon.ico', 'sitemap', 'livewire'];

    public function register(): void
    {
        add_action('parse_request', [$this, 'handle'], 999);
    }

    public function handle(object $wp): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $force = $this->shouldForce404(
            (string) ($_SERVER['REQUEST_URI'] ?? ''),
            $wp->matched_rule ?? null,
            (array) ($wp->query_vars ?? []),
            $this->routeUris(),
        );

        if ($force) {
            // WP::send_headers() sends a real 404 status for this, and WP_Query::parse_query()
            // calls set_404() so the 404 template renders instead of the front page.
            $wp->query_vars = ['error' => '404'];
        }
    }

    /**
     * @param  array<string, mixed>  $queryVars
     * @param  list<string>  $routeUris
     */
    public function shouldForce404(string $requestUri, ?string $matchedRule, array $queryVars, array $routeUris = []): bool
    {
        $path = trim((string) (parse_url($requestUri, PHP_URL_PATH) ?: ''), '/');

        // The front page is legitimately an empty path; a matched rule or explicit query vars
        // (?p=, ?s=, ?feed=) mean WordPress knows what was asked for and 404s on its own.
        if ($path === '' || ! empty($matchedRule) || ! empty($queryVars)) {
            return false;
        }

        $first = strtolower(explode('/', $path)[0]);

        foreach (self::IGNORED_PREFIXES as $prefix) {
            if ($first === $prefix || str_starts_with($first, $prefix.'-') || str_starts_with($first, $prefix.'.')) {
                return false;
            }
        }

        return ! in_array($path, $routeUris, true);
    }

    /**
     * @return list<string>
     */
    protected function routeUris(): array
    {
        try {
            return collect(app('router')->getRoutes()->getRoutes())
                ->map(fn (Route $route) => trim($route->uri(), '/'))
                ->values()
                ->all();
        } catch (Throwable) {
            // A routing lookup must never turn a page request into an error.
            return [];
        }
    }
}

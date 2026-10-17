<?php

namespace App\Infrastructure\Security;

/**
 * Stop anonymous visitors from listing usernames.
 *
 * /?author=1 redirects to /author/<login>/, and /wp-json/wp/v2/users lists every author: half of
 * a brute-force attack, handed over for free. Both stay available to logged-in users, because the
 * block editor needs the users endpoint.
 */
class UserEnumerationGuard
{
    public function register(): void
    {
        add_filter('request', [$this, 'blockAuthorQuery']);
        add_filter('rest_endpoints', [$this, 'hideUserEndpoints']);
    }

    /**
     * @param  array<string, mixed>  $queryVars
     * @return array<string, mixed>
     */
    public function blockAuthorQuery(array $queryVars): array
    {
        if (is_admin() || is_user_logged_in() || ! isset($_GET['author'])) {
            return $queryVars;
        }

        // ?author=<number> is the enumeration vector. Let WordPress render its 404 instead.
        if (is_numeric($_GET['author']) || isset($queryVars['author'])) {
            unset($queryVars['author'], $queryVars['author_name']);
            $queryVars['error'] = '404';
        }

        return $queryVars;
    }

    /**
     * @param  array<string, mixed>  $endpoints
     * @return array<string, mixed>
     */
    public function hideUserEndpoints(array $endpoints): array
    {
        if (is_user_logged_in()) {
            return $endpoints;
        }

        foreach (array_keys($endpoints) as $route) {
            if (preg_match('#^/wp/v2/users(/|$)#', (string) $route)) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }
}

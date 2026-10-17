<?php

namespace App\Infrastructure\Security;

/**
 * Turn XML-RPC off completely.
 *
 * Nothing in this stack uses it (the block editor, the REST API and Application Passwords cover
 * everything it did), and it is the classic brute-force and pingback-amplification target on
 * shared hosting. xmlrpc_enabled alone only disables authenticated methods, so the method list is
 * emptied as well, and the X-Pingback header that advertises the endpoint is removed.
 * web/.htaccess blocks the file at the web server too.
 */
class XmlRpcLockdown
{
    public function register(): void
    {
        add_filter('xmlrpc_enabled', '__return_false');
        add_filter('xmlrpc_methods', '__return_empty_array', PHP_INT_MAX);
        add_filter('wp_headers', [$this, 'removePingbackHeader']);
        add_filter('pings_open', '__return_false', PHP_INT_MAX);
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    public function removePingbackHeader(array $headers): array
    {
        unset($headers['X-Pingback']);

        return $headers;
    }
}

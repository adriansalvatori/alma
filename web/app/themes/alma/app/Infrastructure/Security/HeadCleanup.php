<?php

namespace App\Infrastructure\Security;

/**
 * Remove what only advertises the stack: the WordPress version (a free hint for automated exploit
 * scanners), the XML-RPC discovery links, the shortlink, and the X-Powered-By headers Acorn
 * ("Acorn 6.3.0 (Laravel 13.x)") and PHP add to every response.
 *
 * Performance-related removals (emoji script and styles) belong to the Content module.
 */
class HeadCleanup
{
    public function register(): void
    {
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'wp_shortlink_wp_head');
        remove_action('template_redirect', 'wp_shortlink_header', 11);
        add_filter('the_generator', '__return_empty_string');
        add_filter('wp_headers', [$this, 'removePoweredBy'], PHP_INT_MAX);
        add_action('send_headers', [$this, 'removePhpPoweredBy']);
    }

    /**
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    public function removePoweredBy(array $headers): array
    {
        unset($headers['X-Powered-By']);

        return $headers;
    }

    public function removePhpPoweredBy(): void
    {
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }
    }
}

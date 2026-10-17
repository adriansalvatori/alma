<?php

namespace App\Domains\Lead\Hooks;

use App\Domains\Lead\Services\AttributionTracker;

/**
 * Records the marketing touch of every front-end page view that carries one.
 *
 * template_redirect runs before any output, so the cookie can still be set, and only for real
 * page views: not wp-admin, REST, AJAX, cron or feeds.
 */
class AttributionCookie
{
    public function __construct(private AttributionTracker $tracker) {}

    public function register(): void
    {
        add_action('template_redirect', [$this, 'handle'], 5);
    }

    public function handle(): void
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed() || is_robots() || wp_is_json_request()) {
            return;
        }

        $this->tracker->track(
            wp_unslash($_GET),
            isset($_SERVER['HTTP_REFERER']) ? (string) wp_unslash($_SERVER['HTTP_REFERER']) : null,
            home_url(isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '/'),
        );
    }
}

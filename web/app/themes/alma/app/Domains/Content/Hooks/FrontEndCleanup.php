<?php

namespace App\Domains\Content\Hooks;

/**
 * Front-end weight the theme does not use.
 *
 * - Emoji detection script and styles: every modern browser renders emoji natively.
 * - Core block library CSS and global styles: the theme styles blocks with Tailwind, and these
 *   stylesheets would fight it (app.css re-implements the few core rules the theme relies on,
 *   such as palette colour classes and column stacking).
 * - On-demand block assets are disabled, so WordPress does not inline per-block CSS either.
 */
class FrontEndCleanup
{
    public function register(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        add_filter('emoji_svg_url', '__return_false');

        add_filter('should_load_separate_core_block_assets', '__return_false');
        add_action('wp_enqueue_scripts', [$this, 'dequeueCoreStyles'], 100);
    }

    public function dequeueCoreStyles(): void
    {
        foreach (['wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles'] as $handle) {
            wp_dequeue_style($handle);
        }
    }
}

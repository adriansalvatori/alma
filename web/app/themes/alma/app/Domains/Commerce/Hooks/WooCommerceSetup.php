<?php

namespace App\Domains\Commerce\Hooks;

/**
 * Theme-side WooCommerce setup: declare support, keep WooCommerce on the classic (Blade)
 * template hierarchy, and drop its stylesheets because the shop is styled with Tailwind.
 */
class WooCommerceSetup
{
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'declareSupport'], 20);

        // WooCommerce's block templates would bypass the Blade overrides in
        // resources/views/woocommerce, so keep it on the PHP template hierarchy.
        add_filter('woocommerce_has_block_template', '__return_false');

        add_filter('woocommerce_enqueue_styles', '__return_empty_array');
        add_action('wp_enqueue_scripts', [$this, 'dequeueBlockStyles'], 100);
    }

    public function declareSupport(): void
    {
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

    public function dequeueBlockStyles(): void
    {
        wp_dequeue_style('wc-blocks-style');
    }
}

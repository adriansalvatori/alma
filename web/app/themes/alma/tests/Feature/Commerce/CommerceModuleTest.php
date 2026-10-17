<?php

use App\Domains\Commerce\CommerceServiceProvider;
use App\Domains\Commerce\Hooks\RemoveTemplateHooks;
use App\Domains\Commerce\Hooks\WooCommerceSetup;

beforeEach(function () {
    $GLOBALS['_wp_theme_features'] = [];
});

it('stays off without WooCommerce', function () {
    expect(CommerceServiceProvider::enabled())->toBeFalse()
        ->and(app()->getLoadedProviders())->not->toHaveKey(CommerceServiceProvider::class);
});

it('declares WooCommerce support and keeps WooCommerce on the Blade templates', function () {
    (new WooCommerceSetup)->register();
    do_action('after_setup_theme');

    expect(current_theme_supports('woocommerce'))->toBeTrue()
        ->and(apply_filters('woocommerce_has_block_template', true))->toBeFalse()
        ->and(apply_filters('woocommerce_enqueue_styles', ['woocommerce-general' => []]))->toBe([]);
});

it('unhooks the WooCommerce template hooks listed in config', function () {
    add_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    add_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    add_action('woocommerce_before_main_content', 'woocommerce_breadcrumb_stand_in', 20);

    (new RemoveTemplateHooks)->register();
    do_action('wp');

    expect(has_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper'))->toBeFalse()
        ->and(has_action('woocommerce_sidebar', 'woocommerce_get_sidebar'))->toBeFalse()
        // Hooks not listed in config are left alone.
        ->and(has_action('woocommerce_before_main_content', 'woocommerce_breadcrumb_stand_in'))->toBe(20);
});

function woocommerce_breadcrumb_stand_in(): void {}

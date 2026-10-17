<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Favorites
    |--------------------------------------------------------------------------
    |
    | Logged-in shoppers keep favorites in user meta. Guests keep them in a
    | signed cookie, capped so a forged or runaway cookie cannot grow without
    | bound.
    |
    */

    'favorites' => [
        'cookie' => 'alma_favorites',
        'max_items' => (int) env('COMMERCE_FAVORITES_MAX', 50),
        'lifetime_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | WooCommerce hooks to remove
    |--------------------------------------------------------------------------
    |
    | WooCommerce's default template hooks, unhooked once WordPress has parsed
    | the request. The Blade layout already provides the page wrapper and
    | sidebar, so WooCommerce's own markup for them would be duplicated.
    | Add any [hook, callback, priority] from WooCommerce's
    | includes/wc-template-hooks.php to strip it.
    |
    */

    'remove_hooks' => [
        ['woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10],
        ['woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10],
        ['woocommerce_sidebar', 'woocommerce_get_sidebar', 10],
        ['get_the_generator_html', 'wc_generator_tag', 10],
        ['get_the_generator_xhtml', 'wc_generator_tag', 10],
    ],

];

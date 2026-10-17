<?php

/*
 * Fakes for the Commerce module's tests. WooCommerce itself is never loaded: the functions
 * below exist only so its hook callbacks are valid callables for remove_action().
 */

if (! function_exists('add_theme_support')) {
    function add_theme_support(string $feature, mixed ...$args): bool
    {
        $GLOBALS['_wp_theme_features'][$feature] = $args === [] ? true : $args;

        return true;
    }
}

if (! function_exists('current_theme_supports')) {
    function current_theme_supports(string $feature, mixed ...$args): bool
    {
        return isset($GLOBALS['_wp_theme_features'][$feature]);
    }
}

foreach ([
    'woocommerce_output_content_wrapper',
    'woocommerce_output_content_wrapper_end',
    'woocommerce_get_sidebar',
    'wc_generator_tag',
] as $wooFunction) {
    if (! function_exists($wooFunction)) {
        eval("function {$wooFunction}() {}");
    }
}

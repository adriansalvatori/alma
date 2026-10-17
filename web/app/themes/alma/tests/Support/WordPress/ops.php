<?php

/*
 * Core functions the ops hooks unhook (HeadCleanup) or call. They only need to exist: the fake
 * remove_action() takes a callable, and these names are not callable outside WordPress.
 */

foreach (['wp_generator', 'rsd_link', 'wlwmanifest_link', 'wp_shortlink_wp_head', 'wp_shortlink_header'] as $function) {
    if (! function_exists($function)) {
        eval("function {$function}(): void {}");
    }
}

if (! function_exists('__return_empty_string')) {
    function __return_empty_string(): string
    {
        return '';
    }
}

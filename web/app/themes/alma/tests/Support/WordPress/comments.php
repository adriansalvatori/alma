<?php

/*
 * Fakes for comment support and the wp-admin removal functions DisableComments calls. Post type
 * features live in $GLOBALS['_wp_post_type_features'], as in core.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('post_type_supports')) {
    function post_type_supports(string $type, string $feature): bool
    {
        return isset($GLOBALS['_wp_post_type_features'][$type][$feature]);
    }
}

if (! function_exists('add_post_type_support')) {
    function add_post_type_support(string $type, string|array $features): void
    {
        foreach ((array) $features as $feature) {
            $GLOBALS['_wp_post_type_features'][$type][$feature] = true;
        }
    }
}

if (! function_exists('remove_post_type_support')) {
    function remove_post_type_support(string $type, string $feature): bool
    {
        unset($GLOBALS['_wp_post_type_features'][$type][$feature]);

        return true;
    }
}

if (! function_exists('comments_open')) {
    function comments_open(int|WP_Post|null $post = null): bool
    {
        return (bool) apply_filters('comments_open', true, $post);
    }
}

if (! function_exists('get_comments_number')) {
    function get_comments_number(int|WP_Post $post = 0): string|int
    {
        return apply_filters('get_comments_number', 0, $post);
    }
}

if (! function_exists('comments_template')) {
    function comments_template(string $file = '/comments.php', bool $separateComments = false): void {}
}

if (! function_exists('__return_zero')) {
    function __return_zero(): int
    {
        return 0;
    }
}

if (! function_exists('remove_menu_page')) {
    function remove_menu_page(string $slug): array|false
    {
        WordPress::$options['__removed_menus'][] = $slug;

        return [];
    }
}

if (! function_exists('remove_submenu_page')) {
    function remove_submenu_page(string $parent, string $slug): array|false
    {
        WordPress::$options['__removed_menus'][] = "{$parent} > {$slug}";

        return [];
    }
}

if (! function_exists('remove_meta_box')) {
    function remove_meta_box(string $id, string|array $screen, string $context): void
    {
        WordPress::$options['__removed_meta_boxes'][] = $id;
    }
}

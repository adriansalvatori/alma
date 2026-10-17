<?php

/**
 * Theme setup: supports, menus, sidebars and editor assets.
 *
 * Plain WordPress configuration only. Anything with behaviour lives in a class registered by a
 * module provider (front-end assets are printed by layouts/app.blade.php in a deliberate order).
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Load the theme's styles inside the block editor, so blocks look as they will on the page.
 */
add_filter('block_editor_settings_all', function ($settings) {
    try {
        $style = Vite::asset('resources/css/editor.css');
    } catch (\Throwable) {
        // No build yet (fresh clone before `npm run build`): the editor still works, unstyled.
        return $settings;
    }

    $settings['styles'][] = [
        'css' => "@import url('{$style}');",
    ];

    return $settings;
});

/**
 * Load the editor script, with the wp.* packages it imports (Vite writes them to
 * editor.deps.json; @roots/vite-plugin maps @wordpress/* imports to the globals).
 */
add_action('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    try {
        if (! Vite::isRunningHot()) {
            foreach (json_decode(Vite::content('editor.deps.json'), true) ?: [] as $dependency) {
                if (! wp_script_is($dependency)) {
                    wp_enqueue_script($dependency);
                }
            }
        }

        echo Vite::withEntryPoints(['resources/js/editor.js'])->toHtml();
    } catch (\Throwable) {
        // No build yet.
    }
});

/**
 * Theme supports, menus and editor defaults.
 */
add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'alma'),
        'footer_navigation' => __('Footer Navigation', 'alma'),
    ]);

    // Core's own patterns would sit next to the theme's in the inserter and invite layouts the
    // design system does not cover.
    remove_theme_support('core-block-patterns');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);
}, 20);

/**
 * Widget areas.
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'alma'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'alma'),
        'id' => 'sidebar-footer',
    ] + $config);
});

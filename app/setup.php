<?php

/**
 * Theme setup.
 */

namespace App;

use function Roots\bundle;

$directives      = new \Log1x\SageDirectives\Directives();
$directives_util = new \Log1x\SageDirectives\Util();

/**
 * Register the theme assets.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function (): void {
    /**
     * Cleanup styles
     */
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');

    $localized_object_name = 'sage';
    $localized_vars = [
        'primary' => __('Primary', 'sage')
    ];

    /**
     * Enqueue theme stylesheets
     */
    $namespace = strtolower(wp_get_theme()->get('Name'));
    if (hmr_enabled()) {
        $asset = 'resources/scripts/app.js';
        wp_enqueue_script($namespace, hmr_assets($asset), array(), null, true);
        wp_localize_script($namespace, $localized_object_name, $localized_vars);
    } else {
        bundle('app')->enqueue()->localize($localized_object_name, $localized_vars);
    }
}, 100);


/**
 * Register the theme assets with the block editor.
 *
 * @return void
 */
add_action('enqueue_block_editor_assets', function (): void {
    $namespace = strtolower(wp_get_theme()->get('Name'));
    if (hmr_enabled()) {
        $asset = 'resources/scripts/editor.js';
        $namespace = strtolower(wp_get_theme()->get('Name'));

        wp_enqueue_script($namespace, hmr_assets($asset), array(), null, true);
    } else {
        bundle('editor')->enqueue();
    }
}, 100);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function (): void {
    /**
     * Enable features from the Soil plugin if activated.
     *
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil', [
        'clean-up' => [
            'wp_obscurity',
            'disable_emojis',
            'disable_gutenberg_block_css',
            'disable_extra_rss',
            'disable_recent_comments_css',
            'disable_gallery_css',
            'clean_html5_markup' => false,
        ],
        'nav-walker',
        'disable-asset-versioning',
        'disable-trackbacks',
        'js-to-footer',
        'nice-search',
        'relative-urls'
    ]);

    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage')
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://wordpress.org/gutenberg/handbook/designers-developers/developers/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style'
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/themes/advanced-topics/customizer-api/#theme-support-in-sidebars
     */
    add_theme_support('customize-selective-refresh-widgets');

    /**
     * Register custom image sizes
     *
     * @see app/medias.php
     */
    set_image_sizes();
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>'
    ];

    register_sidebar([
            'name' => __('Primary', 'sage'),
            'id' => 'sidebar-primary'
        ] + $config);

    register_sidebar([
            'name' => __('Footer', 'sage'),
            'id' => 'sidebar-footer'
        ] + $config);
});

add_filter('acorn/router/do_parse_request', function ($do_parse) {
    global $wp_query;
    $wp_query->is_acorn_route = true;

    return $do_parse;
});

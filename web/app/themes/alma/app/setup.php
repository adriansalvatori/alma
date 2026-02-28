<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    if (!get_current_screen()?->is_block_editor()) {
        return;
    }

    if (!Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (!wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register custom block category.
 */
add_filter('block_categories_all', function ($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug' => 'alma',
                'title' => __('Alma Blocks', 'alma'),
                'icon' => 'admin-site',
            ],
        ]
    );
});

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Enable hybrid full-site editing support.
     */
    add_theme_support('block-templates');

    /**
     * Define default block templates for new posts and pages.
     */
    $page_type = get_post_type_object('page');
    if ($page_type) {
        $page_type->template = [
            ['alma/hero', ['title' => 'New Page']],
            ['core/paragraph', ['placeholder' => 'Add your content here...']],
        ];
    }

    $post_type = get_post_type_object('post');
    if ($post_type) {
        $post_type->template = [
            ['core/paragraph', ['placeholder' => 'Start writing...']],
        ];
    }

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
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
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
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
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
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
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Handle initial theme setup upon activation.
 */
add_action('after_switch_theme', function () {
    // 1. Create Default Roles
    $roleService = app(\App\Services\RoleService::class);
    $roleService->createRole('alma_admin', 'Alma Admin', [
        'read' => true,
        'edit_posts' => true,
        'upload_files' => true,
    ]);

    // Helper to create page if it doesn't exist
    $createPage = function ($title, $slug) {
        $page = get_page_by_path($slug);
        if (!$page) {
            return wp_insert_post([
                'post_type' => 'page',
                'post_title' => $title,
                'post_name' => $slug,
                'post_status' => 'publish',
                'post_content' => "<!-- wp:alma/hero {\"title\":\"{$title}\"} /-->",
            ]);
        }
        return $page->ID;
    };

    // 2. Create Home page
    $homeId = $createPage('Home', 'home');

    // 3. Set front page
    if ($homeId) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $homeId);
    }

    // 4. Set permalinks to postname
    update_option('permalink_structure', '/%postname%/');
    flush_rewrite_rules();
});

/**
 * Enqueue frontend assets since FSE bypasses the standard Blade layout.
 */
add_action('wp_head', function () {
    echo \Illuminate\Support\Facades\Blade::render("@vite(['resources/css/app.css', 'resources/js/app.js'])\n@livewireStyles");
    echo \Illuminate\Support\Facades\Blade::render("@fluxAppearance");
}, 5);

add_action('wp_footer', function () {
    echo \Illuminate\Support\Facades\Blade::render("@livewireScripts");
    echo \Illuminate\Support\Facades\Blade::render("@fluxScripts");
}, 5);

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
    try {
        $editorCssUrl = Vite::asset('resources/css/editor.css');

        $settings['styles'][] = [
            'css' => "@import url('{$editorCssUrl}'); .editor-styles-wrapper { background: transparent; }",
        ];
    } catch (\Throwable $e) {
        // Build asset not found yet
    }

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
 * Dequeue native WordPress block styles to prevent conflicts with Tailwind/Flux.
 */
$dequeue_block_styles = function () {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('global-styles'); // Added by theme.json
};
add_action('wp_enqueue_scripts', $dequeue_block_styles, 100);
add_action('admin_enqueue_scripts', $dequeue_block_styles, 100);

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

    /**
     * Enable WooCommerce support.
     */
    add_theme_support('woocommerce');
}, 20);

/**
 * Disable WooCommerce Block Templates.
 * This forces WooCommerce to use standard PHP/Blade template hierarchy.
 */
add_filter('woocommerce_has_block_template', '__return_false');

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
    echo \Illuminate\Support\Facades\Blade::render("@substrateJs");
    echo \Illuminate\Support\Facades\Blade::render("@livewireScripts");
    echo \Illuminate\Support\Facades\Blade::render("@fluxScripts");
}, 5);

/**
 * Intercept Gutenberg Buttons container and format as a flex row with gap.
 */
add_filter('render_block_core/buttons', function ($block_content, $block) {
    if (is_admin()) {
        return $block_content;
    }

    return '<div class="flex flex-wrap items-center gap-3">' . $block_content . '</div>';
}, 10, 2);

/**
 * Intercept Gutenberg Button block and convert to Flux component via Blade runtime.
 */
add_filter('render_block_core/button', function ($block_content, $block) {
    if (is_admin()) {
        return $block_content;
    }

    $href = '';
    $text = '';
    $variant = 'primary'; // Default Flux UI variant

    // Determine variant from Gutenberg styles (e.g. is-style-outline)
    if (isset($block['attrs']['className'])) {
        if (str_contains($block['attrs']['className'], 'is-style-outline')) {
            $variant = 'outline';
        } elseif (str_contains($block['attrs']['className'], 'is-style-ghost')) {
            $variant = 'ghost';
        }
    }

    // Extract href and text from the raw block inner HTML using regex
    if (preg_match('/href="([^"]+)"/', $block['innerHTML'], $hrefMatches)) {
        $href = $hrefMatches[1];
    }

    if (preg_match('/<a[^>]*>(.*?)<\/a>/su', $block['innerHTML'], $textMatches)) {
        $text = strip_tags($textMatches[1]);
    } else {
        // Fallback if no <a> tag
        $text = strip_tags($block['innerHTML']);
    }

    // Default to # if no link
    $href = $href ?: '#';

    // Compile dynamic blade component string
    $bladeString = "<flux:button href=\"{$href}\" variant=\"{$variant}\">{$text}</flux:button>";

    return \Illuminate\Support\Facades\Blade::render($bladeString);
}, 10, 2);

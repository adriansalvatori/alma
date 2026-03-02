<?php

/**
 * Alma Core Architecture Integration (Powered by ACF Pro)
 */

$acf_path = get_theme_file_path('alma-core/');
$acf_url = get_theme_file_uri('alma-core/');

// Customize the url setting to fix incorrect asset URLs
add_filter('acf/settings/url', function () use ($acf_url) {
    return $acf_url;
});

// Disable the ACF Pro updates nag since it's bundled
add_filter('acf/settings/show_updates', '__return_false', 100);

// Rebrand menu items and pages to "Alma Core" instead of "Custom Fields"
add_filter('gettext', function ($translated_text, $text, $domain) {
    if ($domain === 'acf') {
        $rebrands = [
            'ACF' => 'Alma Core',
            'Custom Fields' => 'Alma Core',
            'Advanced Custom Fields' => 'Alma Core Architecture',
            'Field Groups' => 'Alma Field Groups',
            'Add New Field Group' => 'Add Alma Field Group',
            'Edit Field Group' => 'Edit Alma Field Group',
        ];

        if (array_key_exists($text, $rebrands)) {
            return $rebrands[$text];
        }
    }
    return $translated_text;
}, 10, 3);

// Replace "Custom Fields" menu item title explicitly just in case gettext misses it
add_action('admin_menu', function () {
    global $menu;
    if (!is_array($menu)) {
        return;
    }

    foreach ($menu as $key => $value) {
        // ACF uses 'edit.php?post_type=acf-field-group' as the menu slug usually
        if (isset($value[0]) && ($value[0] === 'Custom Fields' || $value[0] === 'ACF')) {
            $menu[$key][0] = 'Alma Core';
            break;
        }
    }
}, 999);

// Hide ACF Logos and branded elements in the admin header
add_action('admin_head', function () {
    echo '<style>
        .acf-headerbar .acf-headerbar-logo,
        .acf-headerbar .acf-headerbar-title,
        .acf-headerbar-actions .acf-headerbar-upgrade,
        .acf-headerbar-actions .acf-headerbar-wp-engine,
        #toplevel_page_edit-post_type-acf-field-group .wp-menu-image::before {
            display: none !important;
        }
        .acf-headerbar {
            padding-left: 20px !important;
        }
    </style>';
});

// Include the ACF plugin
if (file_exists($acf_path . 'acf.php')) {
    include_once($acf_path . 'acf.php');
}

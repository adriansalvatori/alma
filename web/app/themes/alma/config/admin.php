<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin skin
    |--------------------------------------------------------------------------
    |
    | Restyles wp-admin, the toolbar (also on the front end) and wp-login.php
    | from the design tokens in resources/css/tokens.css (AdminTheme). Off
    | leaves WordPress' own look. alma's own screens keep their design system
    | (resources/css/admin/screens.css) either way.
    |
    */

    'theme' => (bool) env('ADMIN_THEME', true),

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | Shown in the toolbar and on the login page. The name defaults to the site
    | name. The mark is a theme-relative image path (an SVG, ideally square);
    | when the file doesn't exist, the name's initial on a brand-coloured
    | square stands in. Colours and the font come from the tokens, never from
    | here: rebrand in resources/css/tokens.css.
    |
    */

    'brand' => [
        'name' => env('ADMIN_BRAND_NAME'),
        'mark' => env('ADMIN_BRAND_MARK', 'resources/images/brand/mark.svg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ACF's admin screens
    |--------------------------------------------------------------------------
    |
    | Field groups are code (ACF Composer), so an editor building one in
    | wp-admin creates a database copy that drifts from git. null shows ACF's
    | menu outside production only; true or false forces it.
    |
    */

    'acf_ui' => env('ADMIN_ACF_UI'),

];

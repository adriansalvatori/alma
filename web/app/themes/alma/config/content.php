<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Livewire navigation
    |--------------------------------------------------------------------------
    |
    | wire:navigate turns internal links into SPA-style page transitions. Off by
    | default: scripts then run once per visit, not per page, so analytics and
    | third-party widgets must listen for livewire:navigated. See Navigate.
    |
    */

    'navigate' => (bool) env('WIRE_NAVIGATE', false),

    /*
    |--------------------------------------------------------------------------
    | Page transitions
    |--------------------------------------------------------------------------
    |
    | The front-end kit's transitions on wire:navigate (js/kit/transitions.js):
    | a card's panel grows into the next page, any other link draws the brand
    | curtain. Needs WIRE_NAVIGATE; off by default. See docs/design-system.md.
    |
    */

    'transitions' => (bool) env('PAGE_TRANSITIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Pattern pages
    |--------------------------------------------------------------------------
    |
    | Pages whose content is a pattern in patterns/. The EnsurePatternPages deploy
    | task creates any that are missing (a page with this slug is never
    | overwritten) and sets the front page when none is set.
    |
    */

    'pages' => [
        'home' => ['title' => 'Home', 'pattern' => 'alma/home', 'front_page' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page inserter
    |--------------------------------------------------------------------------
    |
    | Core blocks offered in the inserter on pages, in addition to every acf/*
    | block. Posts keep the full inserter. See Editor\PageChrome.
    |
    */

    'page_inserter' => [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/quote',
        'core/table',
        'core/image',
        'core/gallery',
        'core/video',
        'core/embed',
        'core/buttons',
        'core/button',
        'core/group',
        'core/columns',
        'core/column',
        'core/spacer',
        'core/separator',
        'core/pattern',
        'core/block',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Generate WebP copies of uploaded JPEG/PNG images (the original stays as
    | uploaded). Needs GD or Imagick with WebP support on the server.
    |
    */

    'webp_uploads' => (bool) env('WEBP_UPLOADS', true),

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | The default is a system font stack: no font download at all. To use a
    | self-hosted font, add its woff2 under resources/fonts, declare it with
    | @font-face in resources/css/app.css, and list the one or two files used
    | above the fold on every page here (Vite paths) to preload them.
    |
    */

    'fonts' => [
        'preload' => [
            // 'resources/fonts/inter-var-latin.woff2',
        ],
    ],

];

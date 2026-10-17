<?php

/*
 * The editor palette (theme.json) and the CSS tokens (app.css) describe the same colours, and
 * every palette colour has the class core's dequeued global-styles used to provide.
 */

require_once __DIR__.'/Support.php';

it('keeps the theme.json brand colour equal to --color-brand-600', function () {
    $css = (string) file_get_contents(theme_path('resources/css/app.css'));
    $palette = collect(json_decode((string) file_get_contents(theme_path('theme.json')), true)['settings']['color']['palette']);

    preg_match('/--color-brand-600:\s*([^;]+);/', $css, $token);

    expect($palette->firstWhere('slug', 'brand')['color'] ?? null)->toBe(trim($token[1] ?? ''));
});

it('defines a text and a background class for every palette colour', function () {
    $css = (string) file_get_contents(theme_path('resources/css/app.css'));
    $palette = json_decode((string) file_get_contents(theme_path('theme.json')), true)['settings']['color']['palette'];
    $missing = [];

    foreach ($palette as $colour) {
        foreach (["has-{$colour['slug']}-color", "has-{$colour['slug']}-background-color"] as $class) {
            if (! str_contains($css, ".{$class}")) {
                $missing[] = $class;
            }
        }
    }

    expect($missing)->toBe([]);
});

it('scans patterns and PHP for Tailwind classes', function () {
    $css = (string) file_get_contents(theme_path('resources/css/app.css'));

    expect($css)->toContain('@source "../../patterns/"')
        ->and($css)->toContain('@source "../../app/"')
        ->and($css)->toContain('@source "../views/"');
});

<?php

/*
 * Pages are patterns, and patterns are built from blocks.
 *
 * Inline markup in a pattern cannot be edited by anyone and never receives a fix made to the
 * block it imitates, so a loop that hand-builds cards (an image and a heading per item) must say
 * why no block fits, in a `// @bespoke: <reason>` comment within the 12 lines above it.
 */

require_once __DIR__.'/Support.php';

use App\Domains\Content\Patterns\ThemePatterns;
use App\Support\BlockMarkup;
use Tests\Support\WordPress\WordPress;

function convention_pattern_files(): array
{
    return [...convention_files('patterns', '.php'), ...convention_files('resources/patterns', '.php')];
}

it('gives every pattern file a title and an alma/ slug', function () {
    $problems = [];

    foreach (convention_files('patterns', '.php') as $file) {
        $headers = get_file_data($file, ['title' => 'Title', 'slug' => 'Slug']);

        if ($headers['title'] === '' || ! str_starts_with($headers['slug'], 'alma/')) {
            $problems[] = convention_relative($file);
        }
    }

    expect($problems)->toBe([]);
});

it('loads every pattern without skipping one', function () {
    $skipped = (new ThemePatterns)->load(theme_path('patterns'));

    expect($skipped)->toBe([])
        ->and(array_keys(WordPress::$blockPatterns))->toContain('alma/home');
});

it('only places blocks that exist', function () {
    $known = array_keys(BlockMarkup::slugs());
    $problems = [];

    foreach (convention_pattern_files() as $file) {
        $source = (string) file_get_contents($file);
        preg_match_all('/acf\/([a-z0-9-]+)/', $source, $markup);
        preg_match_all('/BlockMarkup::block\(\s*[\'"]([a-z0-9-]+)[\'"]/', $source, $bySlug);

        foreach (array_unique([...$markup[1], ...$bySlug[1]]) as $slug) {
            if (! in_array($slug, $known, true)) {
                $problems[] = convention_relative($file).": acf/{$slug}";
            }
        }
    }

    expect($problems)->toBe([]);
});

it('justifies every hand-built card loop with an @bespoke comment', function () {
    $problems = [];

    foreach (convention_pattern_files() as $file) {
        $lines = file($file) ?: [];

        foreach ($lines as $number => $line) {
            if (! preg_match('/\b(foreach|for)\s*\(|@foreach|@for\b/', $line)) {
                continue;
            }

            $body = implode('', array_slice($lines, $number, 40));
            $buildsCards = preg_match('/<img\b|background-image|bg-cover/i', $body) && preg_match('/<h[1-6]\b/i', $body);
            $justified = preg_match('#//\s*@bespoke:\s*\S#', implode('', array_slice($lines, max(0, $number - 12), 13)));

            if ($buildsCards && ! $justified) {
                $problems[] = convention_relative($file).':'.($number + 1);
            }
        }
    }

    expect($problems)->toBe([]);
});

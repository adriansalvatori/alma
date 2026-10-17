<?php

namespace App\Support;

use WP_Block_Patterns_Registry;

/**
 * Render a registered block pattern from Blade or PHP, e.g. a shared footer band:
 *
 *     {!! \App\Support\Pattern::render('alma/contact') !!}
 */
class Pattern
{
    public static function render(string $slug): string
    {
        if (! class_exists(WP_Block_Patterns_Registry::class)) {
            return '';
        }

        $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($slug);

        return $pattern ? do_blocks((string) $pattern['content']) : '';
    }

    /**
     * The pattern slug when $content is exactly one `wp:pattern` reference, otherwise null.
     *
     * "Exactly one" matters: a page holding expanded markup, or a reference followed by anything
     * else, is database content and must be left alone.
     */
    public static function referencedSlug(string $content): ?string
    {
        if (preg_match('#^\s*<!--\s*wp:pattern\s+(\{.*?\})\s*/-->\s*$#s', $content, $match) !== 1) {
            return null;
        }

        $attributes = json_decode($match[1], true);
        $slug = is_array($attributes) ? ($attributes['slug'] ?? null) : null;

        return is_string($slug) && $slug !== '' ? $slug : null;
    }

    /**
     * The one-line content that makes a page render a pattern.
     */
    public static function reference(string $slug): string
    {
        return '<!-- wp:pattern '.BlockMarkup::serialize(['slug' => $slug]).' /-->';
    }
}

<?php

namespace App\Domains\Ai\Support;

use WP_Block_Patterns_Registry;

/**
 * Reads block markup the way an agent needs it: which blocks a page uses, and whether the page is
 * a bare pattern reference (its layout lives in git, in patterns/<slug>.php).
 *
 * It scans the block comments directly instead of calling parse_blocks(): it only needs names, it
 * counts nested blocks for free, and it behaves the same with or without WordPress loaded.
 */
class BlockInspector
{
    private const BLOCK = '/<!--\s+wp:([a-z][a-z0-9_-]*(?:\/[a-z][a-z0-9_-]*)?)(?:\s+(\{.*?\}))?\s+\/?-->/s';

    private const MAX_PATTERN_DEPTH = 5;

    /**
     * The pattern slug when the content is nothing but one pattern reference, else null.
     */
    public function patternSlug(string $content): ?string
    {
        if (! preg_match('/^\s*<!--\s+wp:pattern\s+(\{.*?\})\s+\/-->\s*$/s', $content, $match)) {
            return null;
        }

        $attributes = json_decode($match[1], true);

        return is_array($attributes) && is_string($attributes['slug'] ?? null) ? $attributes['slug'] : null;
    }

    /**
     * Block name => how many times it appears, with referenced patterns expanded.
     *
     * @return array<string, int>
     */
    public function blockCounts(string $content, int $depth = 0): array
    {
        $counts = [];

        preg_match_all(self::BLOCK, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = str_contains($match[1], '/') ? $match[1] : 'core/'.$match[1];

            if ($name === 'core/pattern') {
                $attributes = json_decode($match[2] ?? '', true);
                $slug = is_array($attributes) ? ($attributes['slug'] ?? null) : null;

                if (is_string($slug) && $depth < self::MAX_PATTERN_DEPTH) {
                    foreach ($this->blockCounts($this->patternContent($slug), $depth + 1) as $inner => $count) {
                        $counts[$inner] = ($counts[$inner] ?? 0) + $count;
                    }

                    continue;
                }
            }

            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }

    public function patternContent(string $slug): string
    {
        if (! class_exists(WP_Block_Patterns_Registry::class)) {
            return '';
        }

        $pattern = WP_Block_Patterns_Registry::get_instance()->get_registered($slug);

        return is_array($pattern) ? (string) ($pattern['content'] ?? '') : '';
    }
}

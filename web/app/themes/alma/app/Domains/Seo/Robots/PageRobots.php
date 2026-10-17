<?php

namespace App\Domains\Seo\Robots;

/**
 * Which pages ask search engines not to index them.
 *
 * The decision lives in the content, not in an SEO plugin's post meta, so it travels with the
 * page: a marker comment in the page or in the pattern the page renders.
 *
 *   <!-- alma:noindex -->         noindex, nofollow
 *   <!-- alma:noindex-follow -->  noindex, follow (thank-you pages: hide them, keep their links)
 *
 * A per-page override is possible through the _alma_robots post meta (noindex, noindex-follow,
 * or index to ignore a marker inherited from a pattern). Acorn routes call forceNoindex().
 *
 * Non-production environments are noindexed as a whole by Bedrock's DISALLOW_INDEXING, so this
 * is only about pages that must stay out of the index on production.
 */
class PageRobots
{
    public const NOINDEX = 'noindex';

    public const NOINDEX_FOLLOW = 'noindex-follow';

    public const META_KEY = '_alma_robots';

    private const MARKER = '/<!--\s*alma:(noindex(?:-follow)?)\s*-->/';

    private const PATTERN_REFERENCE = '/<!--\s*wp:pattern\s+(\{.*?\})\s*\/?-->/s';

    private static ?string $forced = null;

    /**
     * Noindex the current request, for pages WordPress does not know about (Acorn routes).
     */
    public static function forceNoindex(bool $follow = false): void
    {
        self::$forced = $follow ? self::NOINDEX_FOLLOW : self::NOINDEX;
    }

    public static function forced(): ?string
    {
        return self::$forced;
    }

    public static function reset(): void
    {
        self::$forced = null;
    }

    /**
     * The directive for a post: noindex, noindex-follow, or null to leave it indexable.
     */
    public function directiveFor(int $postId): ?string
    {
        if ($postId <= 0) {
            return null;
        }

        $override = (string) get_post_meta($postId, self::META_KEY, true);

        if ($override === 'index') {
            return null;
        }

        if (in_array($override, [self::NOINDEX, self::NOINDEX_FOLLOW], true)) {
            return $override;
        }

        return $this->directiveIn((string) get_post_field('post_content', $postId));
    }

    /**
     * The directive found in block markup, following pattern references one level deep (a page
     * whose content is a single <!-- wp:pattern --> inherits the pattern's marker).
     */
    public function directiveIn(string $content, int $depth = 0): ?string
    {
        if (preg_match(self::MARKER, $content, $match)) {
            return $match[1];
        }

        if ($depth > 1 || ! preg_match_all(self::PATTERN_REFERENCE, $content, $references)) {
            return null;
        }

        foreach ($references[1] as $json) {
            $slug = json_decode($json, true)['slug'] ?? null;

            if (is_string($slug) && ($directive = $this->directiveIn($this->patternContent($slug), $depth + 1))) {
                return $directive;
            }
        }

        return null;
    }

    /**
     * The directive for the current request: forced (Acorn route) or the queried singular post.
     */
    public function current(): ?string
    {
        if (self::$forced !== null) {
            return self::$forced;
        }

        return is_singular() ? $this->directiveFor(get_queried_object_id()) : null;
    }

    protected function patternContent(string $slug): string
    {
        if (! class_exists(\WP_Block_Patterns_Registry::class)) {
            return '';
        }

        $pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered($slug);

        return is_array($pattern) ? (string) ($pattern['content'] ?? '') : '';
    }
}

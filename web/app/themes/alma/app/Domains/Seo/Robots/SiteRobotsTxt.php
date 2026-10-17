<?php

namespace App\Domains\Seo\Robots;

/**
 * robots.txt, generated per request for the host being served (no static file to forget on a
 * new domain).
 *
 * Runs after The SEO Framework (priority 10) and core, so it keeps their output, including the
 * sitemap line and TSF's optional AI-crawler block, and appends the theme's machine endpoints.
 *
 * Only endpoints with no page behind them are disallowed. A page blocked in robots.txt can never
 * be crawled, so its noindex is never seen and it can stay in results as a bare URL. Pages are
 * kept out of the index with the <!-- alma:noindex --> marker instead (see PageRobots).
 */
class SiteRobotsTxt
{
    public function register(): void
    {
        add_filter('robots_txt', [$this, 'filter'], 20, 2);
    }

    public function filter(mixed $output, mixed $public = true): string
    {
        $output = rtrim((string) $output);
        $disallow = array_values(array_unique(array_filter((array) config('seo.robots.disallow', []))));

        // Appended whatever $public says: Bedrock's DISALLOW_INDEXING makes blog_public an int 0,
        // which core's strict '0' check misses, so core prints its normal rules either way.
        // Non-production stays out of the index through the noindex meta tag instead.
        if ($disallow === []) {
            return $output."\n";
        }

        $lines = [$output, '', '# Machine endpoints with no page to index.', 'User-agent: *'];

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        return ltrim(implode("\n", $lines))."\n";
    }
}

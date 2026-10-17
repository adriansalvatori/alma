<?php

namespace App\Domains\Seo\Sitemap;

use App\Domains\Seo\Redirects\RedirectStore;
use App\Domains\Seo\Robots\PageRobots;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Keep pages out of the sitemap when they ask not to be indexed or are redirected away.
 *
 * A sitemap that lists a noindexed or redirected URL is a contradiction search consoles report
 * as an error, and it wastes crawl budget. The SEO Framework caches its sitemap, so the database
 * work here runs once per sitemap build, not per page view.
 */
class SitemapExclusions
{
    public function __construct(
        private readonly PageRobots $robots,
        private readonly RedirectStore $redirects,
    ) {}

    public function register(): void
    {
        add_filter('the_seo_framework_sitemap_exclude_ids', [$this, 'filter']);
        // Core's sitemap, when TSF is not active.
        add_filter('wp_sitemaps_posts_query_args', [$this, 'coreQueryArgs']);
    }

    /**
     * @param  array<int, int>  $excluded
     * @return list<int>
     */
    public function filter(mixed $excluded): array
    {
        return array_values(array_unique(array_map('intval', [...(array) $excluded, ...$this->ids()])));
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function coreQueryArgs(array $args): array
    {
        $args['post__not_in'] = $this->filter($args['post__not_in'] ?? []);

        return $args;
    }

    /**
     * @return list<int>
     */
    public function ids(): array
    {
        return array_values(array_unique([...$this->noindexed(), ...$this->redirected()]));
    }

    /**
     * Published posts whose content, pattern or override asks for noindex. The LIKE narrows the
     * candidates; PageRobots makes the actual decision, so a marker in a pattern is honoured too.
     *
     * @return list<int>
     */
    protected function noindexed(): array
    {
        try {
            $candidates = DB::table('posts')
                ->where('post_status', 'publish')
                ->where(fn ($query) => $query
                    ->where('post_content', 'like', '%alma:noindex%')
                    ->orWhere('post_content', 'like', '%wp:pattern%'))
                ->pluck('ID')
                ->merge(DB::table('postmeta')->where('meta_key', PageRobots::META_KEY)->pluck('post_id'))
                ->map(fn ($id) => (int) $id)
                ->unique();
        } catch (Throwable) {
            return [];
        }

        return $candidates->filter(fn (int $id) => $this->robots->directiveFor($id) !== null)->values()->all();
    }

    /**
     * Posts whose own URL is the source of a redirect.
     *
     * @return list<int>
     */
    protected function redirected(): array
    {
        if (! function_exists('url_to_postid')) {
            return [];
        }

        $ids = [];

        foreach (array_keys($this->redirects->map()) as $path) {
            if ($id = url_to_postid(home_url($path))) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}

<?php

use App\Domains\Seo\Robots\PageRobots;
use App\Domains\Seo\Robots\RobotsMeta;
use App\Domains\Seo\Robots\SiteRobotsTxt;
use Tests\Support\WordPress\WordPress;

beforeEach(fn () => PageRobots::reset());
afterEach(fn () => PageRobots::reset());

describe('robots.txt', function () {
    it('keeps the existing output (sitemap line included) and appends machine endpoints', function () {
        $core = "User-agent: *\nDisallow: /wp/wp-admin/\nAllow: /wp/wp-admin/admin-ajax.php\n\nSitemap: https://example.test/sitemap.xml\n";

        $output = apply_filters('robots_txt', $core, true);

        expect($output)->toStartWith("User-agent: *\nDisallow: /wp/wp-admin/")
            ->toContain('Sitemap: https://example.test/sitemap.xml')
            ->toContain("Disallow: /api/\nDisallow: /livewire/")
            ->toEndWith("\n");
    });

    it('never disallows pages, only configured machine endpoints', function () {
        $output = (new SiteRobotsTxt)->filter('', true);

        expect($output)->not->toContain('Disallow: /'."\n")
            ->and(substr_count($output, 'Disallow:'))->toBe(count(config('seo.robots.disallow')));
    });

    it('appends the machine endpoints even when the site discourages indexing', function () {
        // Bedrock's DISALLOW_INDEXING passes an int 0 here while core still prints its normal rules.
        expect((new SiteRobotsTxt)->filter("User-agent: *\nDisallow: /wp/wp-admin/\n", 0))->toContain('Disallow: /api/');
    });

    it('adds nothing when no endpoints are configured', function () {
        config(['seo.robots.disallow' => []]);

        expect((new SiteRobotsTxt)->filter("User-agent: *\n", true))->toBe("User-agent: *\n");
    });
});

describe('noindex markers', function () {
    it('reads the directive from a marker in the content', function () {
        $robots = new PageRobots;

        expect($robots->directiveIn('<p>Hi</p><!-- alma:noindex -->'))->toBe('noindex')
            ->and($robots->directiveIn('<!--alma:noindex-follow-->'))->toBe('noindex-follow')
            ->and($robots->directiveIn('<p>No marker</p>'))->toBeNull();
    });

    it('inherits the marker of the pattern a page renders', function () {
        $robots = new class extends PageRobots
        {
            protected function patternContent(string $slug): string
            {
                return $slug === 'alma/thank-you' ? '<!-- alma:noindex-follow --><!-- wp:paragraph -->' : '';
            }
        };

        expect($robots->directiveIn('<!-- wp:pattern {"slug":"alma/thank-you"} /-->'))->toBe('noindex-follow')
            ->and($robots->directiveIn('<!-- wp:pattern {"slug":"alma/home"} /-->'))->toBeNull();
    });

    it('lets post meta override the content either way', function () {
        $marked = WordPress::post(['post_content' => '<!-- alma:noindex -->']);
        $plain = WordPress::post(['post_content' => '<p>Plain</p>']);
        update_post_meta($marked->ID, PageRobots::META_KEY, 'index');
        update_post_meta($plain->ID, PageRobots::META_KEY, 'noindex-follow');

        $robots = new PageRobots;

        expect($robots->directiveFor($marked->ID))->toBeNull()
            ->and($robots->directiveFor($plain->ID))->toBe('noindex-follow');
    });

    it('noindexes the queried singular page through core wp_robots', function () {
        $post = WordPress::post(['post_content' => '<!-- alma:noindex -->']);
        WordPress::$conditionals = ['is_singular' => true, 'queried_object' => $post];

        $robots = apply_filters('wp_robots', ['max-image-preview' => 'large']);

        expect($robots)->toMatchArray(['noindex' => true, 'nofollow' => true])
            ->not->toHaveKey('max-image-preview');
    });

    it('keeps links followed for noindex-follow', function () {
        $post = WordPress::post(['post_content' => '<!-- alma:noindex-follow -->']);
        WordPress::$conditionals = ['is_singular' => true, 'queried_object' => $post];

        expect(apply_filters('wp_robots', []))->toBe(['noindex' => true, 'follow' => true]);
    });

    it('applies to The SEO Framework, for the current query and for a specific post', function () {
        $post = WordPress::post(['post_content' => '<!-- alma:noindex -->']);
        $meta = app(RobotsMeta::class);

        WordPress::$conditionals = ['is_singular' => true, 'queried_object' => $post];
        expect($meta->theSeoFramework([], null))->toBe(['noindex' => 'noindex', 'nofollow' => 'nofollow']);

        WordPress::$conditionals = [];
        expect($meta->theSeoFramework([], ['id' => $post->ID, 'tax' => '']))->toBe(['noindex' => 'noindex', 'nofollow' => 'nofollow'])
            ->and($meta->theSeoFramework(['noindex' => ''], ['id' => 5, 'tax' => 'category']))->toBe(['noindex' => '']);
    });

    it('noindexes a request that forced it (Acorn routes)', function () {
        PageRobots::forceNoindex();

        expect(apply_filters('wp_robots', []))->toMatchArray(['noindex' => true]);
    });
});

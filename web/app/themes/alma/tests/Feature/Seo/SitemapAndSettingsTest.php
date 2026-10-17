<?php

use App\Domains\Seo\Robots\PageRobots;
use App\Domains\Seo\Settings\ApplySeoSettings;
use App\Domains\Seo\Settings\TsfConfigurator;
use App\Domains\Seo\Sitemap\SitemapExclusions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\WordPress\WordPress;

describe('sitemap exclusions', function () {
    beforeEach(function () {
        // WordPress' own tables, as far as the sitemap query reads them.
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->longText('post_content');
            $table->string('post_status');
        });
        Schema::create('postmeta', function (Blueprint $table) {
            $table->bigIncrements('meta_id');
            $table->unsignedBigInteger('post_id');
            $table->string('meta_key');
        });
    });

    it('excludes noindexed and redirected posts from TSF and core sitemaps', function () {
        $hidden = WordPress::post(['post_content' => '<!-- alma:noindex -->']);
        $visible = WordPress::post(['post_content' => '<p>Index me</p>']);
        $redirected = WordPress::post(['post_name' => 'old-offer', 'post_content' => '<p>Old</p>']);
        $overridden = WordPress::post(['post_content' => '<p>Plain</p>']);
        update_post_meta($overridden->ID, PageRobots::META_KEY, 'noindex');

        foreach ([$hidden, $visible, $redirected, $overridden] as $post) {
            DB::table('posts')->insert(['ID' => $post->ID, 'post_content' => $post->post_content, 'post_status' => 'publish']);
        }
        DB::table('postmeta')->insert(['post_id' => $overridden->ID, 'meta_key' => PageRobots::META_KEY]);
        config(['redirects' => ['/old-offer' => '/offers/']]);

        $excluded = apply_filters('the_seo_framework_sitemap_exclude_ids', [999]);

        expect($excluded)->toContain(999, $hidden->ID, $redirected->ID, $overridden->ID)
            ->not->toContain($visible->ID)
            ->and(app(SitemapExclusions::class)->coreQueryArgs([])['post__not_in'])->toContain($hidden->ID);
    });
});

describe('TSF settings as code', function () {
    beforeEach(function () {
        $this->defaults = ['title_separator' => 'hyphen', 'author_noindex' => 0, 'og_tags' => 1, 'noindex_post_types' => ['attachment' => 1]];
        $this->configurator = new TsfConfigurator(fn () => $this->defaults);
    });

    it('plans only real changes, comparing 1 and "1" as equal', function () {
        update_option(TsfConfigurator::OPTION, ['title_separator' => 'hyphen', 'author_noindex' => '0', 'og_tags' => '1']);

        $plan = $this->configurator->plan(['title_separator' => 'pipe', 'author_noindex' => 1, 'og_tags' => 1]);

        expect($plan['changes'])->toBe([
            'title_separator' => ['from' => 'hyphen', 'to' => 'pipe'],
            'author_noindex' => ['from' => '0', 'to' => 1],
        ])->and($plan['unknown'])->toBe([]);
    });

    it('writes only the listed keys and leaves the rest of the option alone', function () {
        update_option(TsfConfigurator::OPTION, ['title_separator' => 'hyphen', 'twitter_site' => '@editor-set']);

        $this->configurator->apply(['title_separator' => 'pipe']);

        expect(get_option(TsfConfigurator::OPTION))->toBe(['title_separator' => 'pipe', 'twitter_site' => '@editor-set']);
    });

    it('refuses unknown keys instead of writing them', function () {
        $plan = $this->configurator->apply(['title_seperator' => 'pipe']);

        expect($plan['unknown'])->toBe(['title_seperator'])
            ->and(get_option(TsfConfigurator::OPTION))->toBeFalse();
    });

    it('starts from TSF defaults on a site that never saved its settings', function () {
        $this->configurator->apply(['og_tags' => 0]);

        expect(get_option(TsfConfigurator::OPTION))->toMatchArray(['og_tags' => 0, 'title_separator' => 'hyphen']);
    });

    it('is idempotent', function () {
        $this->configurator->apply(['title_separator' => 'pipe']);

        expect($this->configurator->plan(['title_separator' => 'pipe'])['changes'])->toBe([]);
    });

    it('does nothing when The SEO Framework is not active', function () {
        $configurator = new TsfConfigurator(fn () => null);

        expect($configurator->available())->toBeFalse()
            ->and($configurator->apply(['title_separator' => 'pipe'])['available'])->toBeFalse()
            ->and(get_option(TsfConfigurator::OPTION))->toBeFalse();
    });

    it('ships a configuration whose keys all exist in TSF 5.2', function () {
        $setup = dirname(__DIR__, 7).'/web/app/plugins/autodescription/inc/classes/data/plugin/setup.class.php';

        if (! is_file($setup)) {
            skipUnlessCi('The SEO Framework is not installed.');
        }

        $source = (string) file_get_contents($setup);

        foreach (array_keys(config('seo.tsf.settings')) as $key) {
            $known = str_contains($source, "'{$key}'")
                || in_array($key, ['noindex_post_types', 'noindex_taxonomies'], true); // Built by Helper::get_robots_option_index().

            expect($known)->toBeTrue("TSF has no setting named {$key}");
        }
    });

    it('reports unknown keys from the deploy task without failing the deploy', function () {
        app()->instance(TsfConfigurator::class, $this->configurator);
        config(['seo.tsf.settings' => ['made_up' => 1]]);

        $task = app(ApplySeoSettings::class);

        expect($task->fatal())->toBeFalse()
            ->and(fn () => $task->run())->toThrow(RuntimeException::class, 'made_up');
    });

    it('shows the plan with --dry-run and writes nothing', function () {
        app()->instance(TsfConfigurator::class, $this->configurator);
        config(['seo.tsf.settings' => ['title_separator' => 'pipe']]);

        $this->artisan('alma:seo:apply', ['--dry-run' => true])
            ->expectsOutputToContain('Would change 1 setting(s).')
            ->assertSuccessful();

        expect(get_option(TsfConfigurator::OPTION))->toBeFalse();
    });
});

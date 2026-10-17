<?php

use App\Domains\Content\Deploy\EnsurePatternPages;
use App\Domains\Content\Patterns\PatternPageEditing;
use App\Domains\Content\Patterns\ThemePatterns;
use App\Support\Pattern;
use Tests\Support\WordPress\WordPress;

describe('ThemePatterns', function () {
    it('skips a pattern file that throws, and registers the rest', function () {
        $level = ob_get_level();

        $skipped = (new ThemePatterns)->load(__DIR__.'/Fixtures/patterns');

        expect(array_map('basename', array_keys($skipped)))->toBe(['broken.php'])
            ->and(WordPress::$blockPatterns)->toHaveKeys(['alma/fixture-good', 'alma/fixture-page'])
            ->and(WordPress::$blockPatterns)->not->toHaveKey('alma/fixture-broken')
            // The broken file's half-printed output must not leak into the page being rendered.
            ->and(ob_get_level())->toBe($level);
    });

    it('keeps whole pages out of the inserter and offers sections', function () {
        (new ThemePatterns)->load(__DIR__.'/Fixtures/patterns');

        expect(WordPress::$blockPatterns['alma/fixture-page']['inserter'])->toBeFalse()
            ->and(WordPress::$blockPatterns['alma/fixture-good']['inserter'])->toBeTrue();
    });

    it('stops core\'s stale-pattern-cache notice from becoming an exception', function () {
        expect(ThemePatterns::quietStaleCoreCache(true, '_register_theme_block_patterns'))->toBeFalse()
            ->and(ThemePatterns::quietStaleCoreCache(true, 'something_else'))->toBeTrue();
    });
});

describe('Pattern references', function () {
    it('recognises content that is exactly one pattern reference', function () {
        expect(Pattern::referencedSlug('<!-- wp:pattern {"slug":"alma/home"} /-->'))->toBe('alma/home')
            ->and(Pattern::referencedSlug("  <!-- wp:pattern {\"slug\":\"alma/home\"} /-->\n"))->toBe('alma/home')
            ->and(Pattern::referencedSlug('<!-- wp:pattern {"slug":"alma/home"} /--><p>more</p>'))->toBeNull()
            ->and(Pattern::referencedSlug('<!-- wp:paragraph --><p>x</p><!-- /wp:paragraph -->'))->toBeNull();
    });

    it('writes the reference the editor and the seeder expect', function () {
        expect(Pattern::reference('alma/home'))->toBe('<!-- wp:pattern {"slug":"alma/home"} /-->');
    });
});

describe('PatternPageEditing', function () {
    function patternRequest(array $params = []): object
    {
        return new class($params)
        {
            public function __construct(private array $params) {}

            public function get_param(string $key): mixed
            {
                return $this->params[$key] ?? null;
            }
        };
    }

    beforeEach(function () {
        $this->page = WordPress::post(['post_content' => '<!-- wp:pattern {"slug":"alma/home"} /-->']);
    });

    it('keeps the pattern reference when the editor saves expanded blocks', function () {
        $prepared = (object) ['ID' => $this->page->ID, 'post_content' => '<!-- wp:acf/hero {"name":"acf/hero"} /-->', 'post_title' => 'New title'];

        $result = PatternPageEditing::keepPatternReference($prepared, patternRequest());

        expect(property_exists($result, 'post_content'))->toBeFalse()
            ->and($result->post_title)->toBe('New title');
    });

    it('lets the expanded blocks through, and records where the page came from, on detach', function () {
        $prepared = (object) ['ID' => $this->page->ID, 'post_content' => '<!-- wp:acf/hero {"name":"acf/hero"} /-->'];

        $result = PatternPageEditing::keepPatternReference($prepared, patternRequest([PatternPageEditing::DETACH_PARAM => true]));
        PatternPageEditing::recordDetach($this->page);

        expect($result->post_content)->toContain('acf/hero')
            ->and(get_post_meta($this->page->ID, PatternPageEditing::DETACHED_META, true)['pattern'])->toBe('alma/home');
    });

    it('leaves database pages alone', function () {
        $page = WordPress::post(['post_content' => '<!-- wp:paragraph --><p>Hi</p><!-- /wp:paragraph -->']);
        $prepared = (object) ['ID' => $page->ID, 'post_content' => '<!-- wp:paragraph --><p>Changed</p><!-- /wp:paragraph -->'];

        expect(PatternPageEditing::keepPatternReference($prepared, patternRequest())->post_content)->toContain('Changed');
    });
});

describe('EnsurePatternPages', function () {
    it('creates a missing pattern page and makes it the front page when none is set', function () {
        config(['content.pages' => ['home' => ['title' => 'Home', 'pattern' => 'alma/home', 'front_page' => true]]]);

        (new EnsurePatternPages)->run();

        $page = get_page_by_path('home');

        expect($page)->not->toBeNull()
            ->and(stripslashes($page->post_content))->toBe('<!-- wp:pattern {"slug":"alma/home"} /-->')
            ->and((int) get_option('page_on_front'))->toBe($page->ID);
    });

    it('never touches a page that already exists, or a front page that is already set', function () {
        $existing = WordPress::post(['post_name' => 'home', 'post_content' => 'Edited by hand']);
        update_option('page_on_front', 999);
        config(['content.pages' => ['home' => ['title' => 'Home', 'pattern' => 'alma/home', 'front_page' => true]]]);

        (new EnsurePatternPages)->run();

        expect(get_post($existing->ID)->post_content)->toBe('Edited by hand')
            ->and((int) get_option('page_on_front'))->toBe(999);
    });
});

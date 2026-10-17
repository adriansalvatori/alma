<?php

use App\Infrastructure\Observability\Health\HealthChecker;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $this->editor = $this->actingAsWordPressUser(['role' => 'editor']);

    register_block_pattern('alma/home', [
        'title' => 'Home',
        'categories' => ['alma-pages'],
        'content' => '<!-- wp:acf/hero {"name":"acf/hero"} /--><!-- wp:group --><div><!-- wp:acf/faq /--><!-- wp:paragraph --><p>Hi</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);
    register_block_pattern('alma/cta-section', ['title' => 'CTA', 'categories' => ['alma-sections'], 'content' => '<!-- wp:acf/call-to-action /-->']);
    register_block_pattern('core/query-standard', ['title' => 'Core', 'content' => '']);
});

it('lists pages and marks the ones built from a pattern', function () {
    WordPress::post(['post_title' => 'Home', 'post_name' => 'home', 'post_content' => '<!-- wp:pattern {"slug":"alma/home"} /-->']);
    WordPress::post(['post_title' => 'About', 'post_name' => 'about', 'post_content' => '<!-- wp:paragraph --><p>About</p><!-- /wp:paragraph -->']);
    WordPress::post(['post_title' => 'A Post', 'post_type' => 'post']);

    $pages = ai_test_run_ability('alma/list-pages');

    expect(array_column($pages, 'title'))->toBe(['About', 'Home'])
        ->and(array_column($pages, 'pattern', 'slug'))->toBe(['about' => null, 'home' => 'alma/home']);
});

it('describes a pattern page as code-owned, with the blocks its pattern uses', function () {
    $page = WordPress::post(['post_title' => 'Home', 'post_name' => 'home', 'post_content' => '<!-- wp:pattern {"slug":"alma/home"} /-->']);

    $description = ai_test_run_ability('alma/describe-page', ['slug' => 'home']);

    expect($description['id'])->toBe($page->ID)
        ->and($description['code_owned'])->toBeTrue()
        ->and($description['pattern'])->toBe(['slug' => 'alma/home', 'file' => 'patterns/home.php', 'registered' => true])
        ->and(array_column($description['blocks'], 'count', 'name'))->toEqualCanonicalizing([
            'acf/hero' => 1, 'core/group' => 1, 'acf/faq' => 1, 'core/paragraph' => 1,
        ]);
});

it('describes a detached page as database content', function () {
    $page = WordPress::post(['post_name' => 'pricing', 'post_content' => '<!-- wp:acf/hero /--><!-- wp:pattern {"slug":"alma/cta-section"} /-->']);
    update_post_meta($page->ID, '_alma_detached_from_pattern', ['pattern' => 'alma/pricing']);

    $description = ai_test_run_ability('alma/describe-page', ['id' => $page->ID]);

    expect($description['code_owned'])->toBeFalse()
        ->and($description['detached'])->toBe(['pattern' => 'alma/pricing'])
        ->and(array_column($description['blocks'], 'name'))->toEqualCanonicalizing(['acf/hero', 'acf/call-to-action']);
});

it('lists the theme\'s patterns, and every pattern when asked', function () {
    $theme = ai_test_run_ability('alma/list-patterns');
    $all = ai_test_run_ability('alma/list-patterns', ['all' => true]);

    expect(array_column($theme, 'page', 'name'))->toBe(['alma/cta-section' => false, 'alma/home' => true])
        ->and($all)->toHaveCount(3);
});

it('lists the ACF blocks with their fields', function () {
    WordPress::$options['__block_types'] = [
        'acf/hero' => ['title' => 'Hero', 'description' => 'The page opener.'],
        'core/paragraph' => ['title' => 'Paragraph'],
    ];
    WordPress::$options['__acf_field_groups']['acf/hero'] = [['name' => 'headline', 'label' => 'Headline', 'type' => 'text']];

    expect(ai_test_run_ability('alma/list-blocks'))->toBe([[
        'name' => 'acf/hero',
        'title' => 'Hero',
        'description' => 'The page opener.',
        'fields' => [['name' => 'headline', 'label' => 'Headline', 'type' => 'text']],
    ]]);
});

it('drafts a post from Markdown as real blocks', function () {
    $result = ai_test_run_ability('alma/create-draft-post', [
        'title' => 'Hiring remote teams',
        'content' => "## Why it works\n\nTeams **ship**.\n\n- Faster\n- Cheaper",
    ]);

    $post = get_post($result['id']);
    $content = wp_unslash($post->post_content);

    expect($result['status'])->toBe('draft')
        ->and($post->post_status)->toBe('draft')
        ->and($post->post_author)->toBe($this->editor->ID)
        ->and($content)->toContain('<!-- wp:heading -->')
        ->toContain('<strong>ship</strong>')
        ->toContain('<!-- wp:list-item -->');
});

it('will not publish for a user who may not', function () {
    $this->editor->add_cap('publish_posts', false);

    expect(ai_test_run_ability('alma/create-draft-post', ['title' => 'Live', 'status' => 'publish']))->toBeInstanceOf(WP_Error::class)
        ->and(get_posts(['post_type' => 'post', 'post_status' => 'any']))->toBe([]);
});

it('reports the site\'s state section by section', function () {
    $health = ai_test_run_ability('alma/site-health');

    expect($health)->toHaveKeys(['site', 'modules', 'plugins', 'cron', 'checks'])
        ->and($health['site']['release'])->toBe('test')
        ->and($health['modules'])->toContain('ai', 'lead');
});

it('keeps reporting when one section fails', function () {
    app()->bind(HealthChecker::class, fn () => throw new RuntimeException('checker down'));

    $health = ai_test_run_ability('alma/site-health');

    expect($health['checks'])->toBe(['error' => 'checker down'])
        ->and($health['site'])->toHaveKey('release');
});

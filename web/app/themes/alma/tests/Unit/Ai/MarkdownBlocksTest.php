<?php

use App\Domains\Ai\Support\MarkdownBlocks;

beforeEach(function () {
    $this->markdown = new MarkdownBlocks;
});

it('turns plain text into paragraphs', function () {
    expect($this->markdown->convert("One line\nwraps here.\n\nSecond paragraph."))
        ->toBe("<!-- wp:paragraph -->\n<p>One line wraps here.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:paragraph -->\n<p>Second paragraph.</p>\n<!-- /wp:paragraph -->");
});

it('writes h2 headings without a level attribute and others with one', function () {
    expect($this->markdown->convert('## Title'))->toContain('<!-- wp:heading -->')->toContain('<h2 class="wp-block-heading">Title</h2>')
        ->and($this->markdown->convert('### Sub'))->toContain('<!-- wp:heading {"level":3} -->')->toContain('<h3');
});

it('builds bulleted and numbered lists from list-item blocks', function () {
    expect($this->markdown->convert("- a\n- b"))->toContain('<ul class="wp-block-list">')->toContain('<li>a</li>')
        ->and($this->markdown->convert("1. a\n2. b"))->toContain('<!-- wp:list {"ordered":true} -->')->toContain('<ol');
});

it('escapes HTML so an agent cannot inject markup', function () {
    expect($this->markdown->inline('<script>alert(1)</script> **x**'))
        ->toBe('&lt;script&gt;alert(1)&lt;/script&gt; <strong>x</strong>');
});

it('links only to safe URLs', function () {
    expect($this->markdown->inline('[ok](https://example.com) [bad](javascript:alert(1))'))
        ->toContain('<a href="https://example.com">ok</a>')
        ->toContain('[bad](javascript:alert(1))')
        ->not->toContain('href="javascript');
});

it('handles emphasis and inline code', function () {
    expect($this->markdown->inline('*a* _b_ `c`'))->toBe('<em>a</em> <em>b</em> <code>c</code>');
});

it('returns nothing for empty input', function () {
    expect($this->markdown->convert("  \n "))->toBe('');
});

<?php

/*
 * The front-end kit's components (resources/views/components/kit, docs/design-system.md →
 * "Front-end kit"): each renders on its own with the markup its CSS and scripts rely on.
 */

use Illuminate\Support\Facades\Blade;

it('renders the pill in each variant, with its arrow unless asked not to', function () {
    expect(Blade::render('<x-kit.pill href="/go">Go</x-kit.pill>'))->toContain('href="/go"')->toContain('bg-zinc-950')->toContain('→')
        ->and(Blade::render('<x-kit.pill href="/go" variant="brand">Go</x-kit.pill>'))->toContain('bg-brand-500')
        ->and(Blade::render('<x-kit.pill href="/go" variant="outline" :arrow="false">Go</x-kit.pill>'))->toContain('border-current')->not->toContain('→');
});

it('renders the arrow link with its hidden arrow and the text', function () {
    $html = Blade::render('<x-kit.arrow-link href="/work">Work</x-kit.arrow-link>');

    expect($html)->toContain('class="kit-arrow-link"')->toContain('aria-hidden="true"')->toContain('<span>Work</span>');
});

it('renders the marquee twice over, hidden from assistive tech', function () {
    $html = Blade::render('<x-kit.marquee :items="[\'Brand\', \'Web\']" reverse />');

    expect(substr_count($html, 'Brand'))->toBe(2)
        ->and($html)->toContain('aria-hidden="true"')->toContain('is-reverse');
});

it('renders the carousel with its card widths and items', function () {
    $html = Blade::render('<x-kit.carousel inset width="30vw"><x-kit.carousel-item>One</x-kit.carousel-item></x-kit.carousel>');

    expect($html)->toContain('kit-carousel is-inset')->toContain('--kit-carousel-width: 30vw')->toContain('kit-carousel-item');
});

it('renders the menu closed and inert, with every link', function () {
    $html = Blade::render('<x-kit.menu :links="[[\'label\' => \'Work\', \'url\' => \'/work\'], [\'label\' => \'About\', \'url\' => \'/about\']]" />');

    expect($html)->toContain('id="kit-menu"')->toContain(' inert')->toContain('href="/work"')->toContain('href="/about"')->toContain('toggleKitMenu');
});

it('renders a media card as a morph source, a hover video only when it has a video', function () {
    expect(Blade::render('<x-kit.media-card href="/p" title="Project" image="/a.webp" />'))->toContain('data-morph')->toContain('data-morph-media')->not->toContain('data-hover-video')
        ->and(Blade::render('<x-kit.media-card href="/p" title="Project" image="/a.webp" video="/a.mp4" meta="Website" />'))->toContain('data-hover-video')->toContain('preload="none"')->toContain('Website');
});

it('renders a document page with numbered sections and kept links', function () {
    $html = Blade::render('<x-kit.document title="Terms" :sections="[[\'title\' => \'Scope\', \'body\' => [\'See <a href=\\\'/x\\\'>this</a>.\']], [\'title\' => \'Rights\', \'body\' => [\'Yours.\']]]" />');

    expect($html)->toContain('(01)')->toContain('(02)')->toContain('<a href=\'/x\'>this</a>');
});

<?php

use Illuminate\Support\Facades\Blade;
use Tests\Support\WordPress\WordPress;

it('renders the search form on a page WordPress renders (no web middleware, no session errors)', function () {
    $html = view('forms.search')->render();

    expect($html)->toContain('name="s"')->and($html)->toContain('role="search"');
});

it('renders the header for a visitor, with the account links and no commerce actions', function () {
    $html = view('sections.header', ['siteName' => 'Alma'])->render();

    expect($html)->toContain(route('login'))
        ->and($html)->not->toContain('livewire:commerce')
        ->and($html)->not->toContain('wire:navigate');
});

it('renders the header for a signed-in user with a nonce-protected logout link', function () {
    $this->actingAsWordPressUser(['display_name' => 'Ada']);

    $html = view('sections.header', ['siteName' => 'Alma'])->render();

    expect($html)->toContain('Ada')
        ->and($html)->toContain(route('dashboard'))
        ->and($html)->toContain('action=logout');
});

it('adds wire:navigate to links only when it is switched on', function () {
    config(['content.navigate' => true]);

    expect(view('sections.footer', ['siteName' => 'Alma'])->render())->toContain('wire:navigate');
});

it('renders the footer menu-less without hard-coded placeholder links', function () {
    WordPress::$options['blogdescription'] = 'Tagline here';

    $html = view('sections.footer', ['siteName' => 'Alma'])->render();

    expect($html)->toContain('Tagline here')->and($html)->not->toContain('href="#"');
});

it('boots the Content module completely (Acorn silently skips a provider whose boot throws)', function () {
    expect(view()->getShared())->toHaveKey('errors')
        ->and(Blade::getCustomDirectives())->toHaveKey('navigate');
});

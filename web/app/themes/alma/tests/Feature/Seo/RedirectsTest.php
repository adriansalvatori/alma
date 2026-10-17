<?php

use App\Domains\Seo\Admin\RedirectsScreen;
use App\Domains\Seo\Redirects\ApplyRedirects;
use App\Domains\Seo\Redirects\Redirect;
use App\Domains\Seo\Redirects\RedirectManager;
use App\Domains\Seo\Redirects\RedirectPath;
use App\Domains\Seo\Redirects\RedirectRejected;
use App\Domains\Seo\Redirects\RedirectStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

it('normalizes paths so case, slashes and absolute same-host URLs agree', function () {
    expect(RedirectPath::normalize('/Old-Page/'))->toBe('/Old-Page')
        ->and(RedirectPath::key('/Old-Page/'))->toBe('/old-page')
        ->and(RedirectPath::normalize('old//page?x=1'))->toBe('/old/page')
        ->and(RedirectPath::normalize('https://example.test/a/b/', 'example.test'))->toBe('/a/b')
        ->and(RedirectPath::normalize('https://other.test/a', 'example.test'))->toBe('')
        ->and(RedirectPath::normalize('/'))->toBe('/');
});

describe('code redirects', function () {
    it('matches case-insensitively, with or without the trailing slash, and carries the query string', function () {
        config(['redirects' => ['/Old-Pricing' => '/pricing/']]);

        $match = app(ApplyRedirects::class)->match('/old-pricing/?utm_source=ad');

        expect($match)->toBe(['location' => 'https://example.test/pricing/?utm_source=ad', 'status' => 301, 'id' => null]);
    });

    it('supports a custom status and keeps a target query string as is', function () {
        config(['redirects' => ['/promo' => ['to' => '/offers/?ref=promo', 'status' => 302]]]);

        expect(app(ApplyRedirects::class)->match('/promo?x=1'))
            ->toBe(['location' => 'https://example.test/offers/?ref=promo', 'status' => 302, 'id' => null]);
    });

    it('allows the hosts of off-site targets through wp_safe_redirect', function () {
        config(['redirects' => ['/docs' => 'https://docs.example.org/']]);

        expect(apply_filters('allowed_redirect_hosts', ['example.test']))->toContain('docs.example.org');
    });

    it('returns null for paths without a redirect', function () {
        expect(app(ApplyRedirects::class)->match('/nothing-here'))->toBeNull();
    });
});

describe('database redirects', function () {
    beforeEach(fn () => $this->manager = app(RedirectManager::class));

    it('stores a redirect, compiles it into the autoloaded lookup and serves it', function () {
        $redirect = $this->manager->create('/Spring-Sale/', '/offers/', 302, 'Campaign', 7);

        expect($redirect->from_path)->toBe('/Spring-Sale')
            ->and($redirect->from_key)->toBe('/spring-sale')
            ->and(get_option(RedirectStore::OPTION)['map']['/spring-sale']['to'])->toBe('/offers/');

        app()->forgetInstance(RedirectStore::class);

        expect(app(ApplyRedirects::class)->match('/spring-sale'))
            ->toBe(['location' => 'https://example.test/offers/', 'status' => 302, 'id' => $redirect->id]);
    });

    it('stores same-site absolute targets as paths, so a copied database still works', function () {
        expect($this->manager->create('/a', 'https://example.test/b/?x=1')->to_url)->toBe('/b/?x=1');
    });

    it('lets code redirects win over database ones', function () {
        Redirect::query()->create(['from_path' => '/x', 'from_key' => '/x', 'to_url' => '/from-db', 'status_code' => 301]);
        app(RedirectStore::class)->rebuild();
        config(['redirects' => ['/x' => '/from-code']]);
        app()->forgetInstance(RedirectStore::class);

        expect(app(ApplyRedirects::class)->match('/x')['location'])->toBe('https://example.test/from-code');
    });

    it('counts hits', function () {
        $redirect = $this->manager->create('/counted', '/target');

        $this->manager->recordHit($redirect->id);
        $this->manager->recordHit($redirect->id);

        expect($redirect->fresh()->hits)->toBe(2)
            ->and($redirect->fresh()->last_hit_at)->not->toBeNull();
    });

    it('removes a deleted redirect from the lookup', function () {
        $redirect = $this->manager->create('/gone', '/target');
        $this->manager->delete($redirect->id);
        app()->forgetInstance(RedirectStore::class);

        expect(app(ApplyRedirects::class)->match('/gone'))->toBeNull();
    });

    it('refuses redirects that would break the site', function (string $from, string $to, string $message) {
        config(['redirects' => ['/in-code' => '/elsewhere']]);
        $this->manager->create('/existing', '/target');
        $this->manager->create('/b', '/c');

        expect(fn () => $this->manager->create($from, $to))->toThrow(RedirectRejected::class, $message);
    })->with([
        'homepage' => ['/', '/x', 'homepage'],
        'wp-admin' => ['/wp-admin/options.php', '/x', '/wp-admin'],
        'wp-json' => ['/wp-json/wp/v2', '/x', '/wp-json'],
        'livewire' => ['/livewire/update', '/x', '/livewire'],
        'api' => ['/api/health', '/x', '/api'],
        'other host source' => ['https://elsewhere.test/a', '/x', 'path on this site'],
        'insecure target' => ['/a', 'http://partner.test/', 'https://'],
        'relative target' => ['/a', 'pricing', 'Start the target with /'],
        'self' => ['/self', '/self/', 'itself'],
        'loop' => ['/c', '/b', 'loop'],
        'duplicate' => ['/Existing', '/x', 'already redirected'],
        'duplicate of code' => ['/in-code', '/x', 'config/redirects.php'],
        'empty target' => ['/a', '', 'Enter where'],
    ]);

    it('refuses unsupported status codes', function () {
        expect(fn () => $this->manager->create('/a', '/b', 410))->toThrow(RedirectRejected::class);
    });
});

it('lists code and database redirects in Tools → Redirects', function () {
    config(['redirects' => ['/from-code' => '/target']]);
    app(RedirectManager::class)->create('/from-db', '/target');
    app()->forgetInstance(RedirectStore::class);
    $this->actingAsWordPressUser(['role' => 'administrator']);

    ob_start();
    app(RedirectsScreen::class)->render();
    $html = ob_get_clean();

    expect($html)->toContain('/from-db')->toContain('/from-code')->toContain('config/redirects.php')
        ->toContain('name="_wpnonce"');
});

it('renders nothing for users who cannot manage redirects', function () {
    $this->actingAsWordPressUser(['role' => 'editor']);

    ob_start();
    app(RedirectsScreen::class)->render();

    expect(ob_get_clean())->toBe('');
});

it('registers the screen under Tools for administrators only', function () {
    app(RedirectsScreen::class)->menu();

    expect(WordPress::$options['__menu_pages']['alma-redirects']['capability'])->toBe('manage_options');
});

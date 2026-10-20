<?php

/*
 * The wp-admin dashboard (App\Infrastructure\WordPress\Admin\Dashboard): core's blog widgets out,
 * the platform widgets in, each module's live figures and shortcuts merged in through filters.
 */

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\IntegrationLog\IntegrationCall;
use App\Infrastructure\WordPress\Admin\AdminTheme;
use App\Infrastructure\WordPress\Admin\Dashboard\Dashboard;
use App\Infrastructure\WordPress\Admin\Dashboard\PlatformHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

function renderDashboardWidget(callable $render): string
{
    ob_start();
    $render();

    return (string) ob_get_clean();
}

it('replaces core\'s widgets with the platform ones', function () {
    $this->actingAsWordPressUser(['role' => 'administrator']);

    app(Dashboard::class)->setup();

    expect(WordPress::$options['__removed_meta_boxes'])->toContain('dashboard_activity', 'dashboard_right_now', 'dashboard_primary', 'dashboard_quick_press', 'dashboard_site_health')
        ->and(WordPress::$options['__dashboard_widgets'])->toHaveKeys(['alma_dashboard_modules', 'alma_dashboard_site_health', 'alma_dashboard_shortcuts'])
        ->and(WordPress::$options['__dashboard_widgets']['alma_dashboard_modules']['context'])->toBe('side');
});

it('shows the health widget only to people who manage the site', function () {
    $this->actingAsWordPressUser(['role' => 'editor']);

    app(Dashboard::class)->setup();

    expect(WordPress::$options['__dashboard_widgets'])->not->toHaveKey('alma_dashboard_site_health')
        ->and(WordPress::$options['__dashboard_widgets'])->toHaveKey('alma_dashboard_modules');
});

it('lists every module with its status, its summary and what it declares', function () {
    config(['alma.disabled' => ['ai'], 'payments.enabled' => false]);

    $modules = app(Dashboard::class)->modules();

    expect($modules['lead']['status'])->toBe('active')
        ->and($modules['ai']['status'])->toBe('disabled')
        ->and($modules['payment']['status'])->toBe('unavailable')
        ->and($modules['lead']['description'])->toStartWith('Leads: capture')
        ->and($modules['lead']['chips'])->toContain('Tables')
        ->and(implode(' ', $modules['lead']['chips']))->toMatch('/\d+ hooks/');
});

it('lets a module put live figures on its card', function () {
    add_filter('alma/dashboard/modules', function (array $modules) {
        $modules['seo']['chips'] = ['Redirects: 3'];

        return $modules;
    });

    $html = renderDashboardWidget(fn () => app(Dashboard::class)->renderModules());

    expect($html)->toContain('Redirects: 3')
        ->and($html)->toContain('alma-dash-domain-card');
});

it('merges each module\'s shortcuts after core\'s', function () {
    $this->actingAsWordPressUser(['role' => 'administrator']);
    add_filter('alma/dashboard/shortcuts', fn (array $shortcuts) => [...$shortcuts, ['title' => 'Quotes', 'description' => 'Open quotes', 'url' => 'https://example.test/quotes', 'icon' => '']]);

    $html = renderDashboardWidget(fn () => app(Dashboard::class)->renderShortcuts());

    expect($html)->toContain('View live website')
        ->and($html)->toContain('Integration log')
        ->and($html)->toContain('Quotes');
});

it('reports every health check and judges each integration from its recent calls', function () {
    foreach ([200, 500, 500, 500] as $i => $status) {
        IntegrationCall::query()->create([
            'source' => 'laravel', 'method' => 'POST', 'host' => 'hooks.example.test', 'url' => 'https://hooks.example.test/lead',
            'status' => $status, 'duration_ms' => 120, 'created_at' => now()->subMinutes(10 - $i),
        ]);
    }

    IntegrationCall::query()->create([
        'source' => 'wordpress', 'method' => 'GET', 'host' => 'api.example.test', 'url' => 'https://api.example.test/ping',
        'status' => 200, 'duration_ms' => 80, 'created_at' => now(),
    ]);

    $snapshot = app(PlatformHealth::class)->snapshot();
    $hosts = collect($snapshot['integrations'])->keyBy('title');

    expect($snapshot['checks'])->not->toBeEmpty()
        ->and($hosts['hooks.example.test']['status'])->toBe(CheckResult::CRITICAL)
        ->and($hosts['hooks.example.test']['details']['Consecutive failures'])->toBe('3')
        ->and($hosts['hooks.example.test']['details']['Last error'])->toBe('HTTP 500')
        ->and($hosts['api.example.test']['status'])->toBe(CheckResult::OK)
        ->and(PlatformHealth::cachedOverall())->toBe($snapshot['overall']);

    $this->actingAsWordPressUser(['role' => 'administrator']);
    $html = renderDashboardWidget(fn () => app(Dashboard::class)->renderHealth());

    expect($html)->toContain('hooks.example.test')
        ->and($html)->toContain('alma-dash-indicator-red')
        ->and($html)->toContain('PHP '.PHP_VERSION);
});

it('says nothing in the footer about health it hasn\'t seen, and reports what the dashboard saw', function () {
    $theme = app(AdminTheme::class);

    expect($theme->footerText())->toContain('Alma Admin Console')
        ->and($theme->footerText())->not->toContain('operational');

    Cache::put(PlatformHealth::CACHE_KEY, ['overall' => CheckResult::OK], 60);
    expect($theme->footerText())->toContain('All systems operational');

    Cache::put(PlatformHealth::CACHE_KEY, ['overall' => CheckResult::WARNING], 60);
    expect($theme->footerText())->toContain('Some checks need attention');
});

<?php

/*
 * alma's own wp-admin screens are one product: each renders inside <x-admin::page> (the
 * .alma-admin wrapper the design system is scoped to), and the admin sheets carry no trace of the
 * project they were ported from.
 */

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadSettings;
use App\Domains\Seo\Admin\RedirectsScreen;
use App\Infrastructure\Observability\IntegrationLog\IntegrationLogScreen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;

uses(RefreshDatabase::class);

/**
 * Every admin screen view: resources/views/admin/*.blade.php and each module's
 * resources/views/admin/*.blade.php. Partials start with an underscore.
 *
 * @return array<string, string> relative path => contents
 */
function adminScreenViews(): array
{
    $views = [];

    $finder = Finder::create()->files()->name('*.blade.php')->depth(0)
        ->in(array_filter([theme_path('resources/views/admin'), ...glob(theme_path('app/Domains/*/resources/views/admin'))], 'is_dir'));

    foreach ($finder as $file) {
        if (! str_starts_with($file->getFilename(), '_')) {
            $views[str_replace(theme_path().'/', '', $file->getRealPath())] = $file->getContents();
        }
    }

    return $views;
}

it('builds every admin screen view on <x-admin::page>', function () {
    $views = adminScreenViews();

    expect($views)->not->toBeEmpty();

    $missing = array_keys(array_filter($views, fn (string $contents) => ! str_contains($contents, '<x-admin::page')));

    expect($missing)->toBe([]);
});

it('leaves the .wrap to the page component, never to a screen class', function () {
    $offenders = [];

    foreach (Finder::create()->files()->name('*.php')->in(theme_path('app')) as $file) {
        if (preg_match('/class=\\\\?["\']wrap/', $file->getContents())) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([]);
});

it('renders the screens inside .alma-admin', function (Closure $render) {
    $this->actingAsWordPressUser(['role' => 'administrator']);

    expect($render())->toContain('class="wrap alma-admin"');
})->with([
    'Tools → Redirects' => [function () {
        ob_start();
        app(RedirectsScreen::class)->render();

        return (string) ob_get_clean();
    }],
    'Tools → Integration log' => [function () {
        ob_start();
        app(IntegrationLogScreen::class)->render();

        return (string) ob_get_clean();
    }],
    'Leads → Settings' => [fn () => view('lead::admin.settings', ['settings' => app(LeadSettings::class), 'notice' => 'saved', 'status' => 0])->render()],
    'Leads → one lead' => [function () {
        $lead = Lead::query()->create(['name' => 'Ada', 'email' => 'ada@example.com', 'form_key' => 'contact']);
        LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Webhook', 'outcome' => LeadActivity::FAILED]);

        return view('lead::admin.show', ['lead' => $lead->load('activity'), 'statuses' => LeadStatus::cases(), 'updated' => false])->render();
    }],
]);

it('keeps a settings field locked to .env readable and disabled', function () {
    config(['leads.webhook_url' => 'https://hooks.example.test/x']);

    $html = view('lead::admin.settings', ['settings' => app(LeadSettings::class), 'notice' => '', 'status' => 0])->render();

    expect($html)->toContain('<code>LEAD_WEBHOOK_URL</code>')
        ->and($html)->toMatch('/id="alma-webhook-url"[^>]*disabled/');
});

it('ships admin sheets and views with no trace of the project they were ported from', function () {
    $remnants = [];

    // What reaches the browser. PHP comments may name where an idea came from.
    foreach (Finder::create()->files()->in([theme_path('resources/css/admin'), theme_path('resources/views/admin')]) as $file) {
        foreach (['Remote Leverage', 'remote-leverage', 'rl-admin', '.rl-', 'M104.67'] as $needle) {
            if (str_contains($file->getContents(), $needle)) {
                $remnants[] = "{$file->getRelativePathname()}: {$needle}";
            }
        }
    }

    expect($remnants)->toBe([]);
});

it('imports the skin\'s parts in the order its !important ties were written for', function () {
    $css = (string) file_get_contents(theme_path('resources/css/admin/skin.css'));

    preg_match_all('/@import "\.\/([^"]+)"/', $css, $imports);

    expect($imports[1])->toBe(['_tokens.css', 'reset.css', '_toolbar.css', 'console.css']);
});

it('re-asserts anchors drawn as controls above the skin\'s link reset', function () {
    // The skin paints every content-area link at (1,1,1) !important; a class-only rule loses to it.
    $css = (string) file_get_contents(theme_path('resources/css/admin/screens.css'));

    foreach (['alma-btn', 'alma-tab', 'alma-stat', 'alma-audit-pill'] as $control) {
        expect($css)->toMatch('/#wpbody-content \.alma-admin a:is\([^)]*\.'.$control.'[,)]/');
    }

    expect($css)->toContain('#wpbody-content .alma-admin a.alma-btn--primary');
});

it('keeps a notice the drawer must not move on the page', function () {
    $css = (string) file_get_contents(theme_path('resources/css/admin/console.css'));
    $drawer = (string) file_get_contents(theme_path('resources/views/admin/_notifications.blade.php'));

    expect($css)->toContain(':not(.alma-keep-notice)')
        ->and($drawer)->toContain("classList.contains('alma-keep-notice')");
});

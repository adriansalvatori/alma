<?php

namespace App\Domains\Seo;

use App\Domains\Seo\Admin\RedirectsScreen;
use App\Domains\Seo\Commands\ApplySeoSettingsCommand;
use App\Domains\Seo\Redirects\ApplyRedirects;
use App\Domains\Seo\Redirects\RedirectStore;
use App\Domains\Seo\Robots\RobotsMeta;
use App\Domains\Seo\Robots\SiteRobotsTxt;
use App\Domains\Seo\Routing\HttpsRedirect;
use App\Domains\Seo\Routing\MissingPathNotFound;
use App\Domains\Seo\Routing\RouteSeo;
use App\Domains\Seo\Settings\ApplySeoSettings;
use App\Domains\Seo\Sitemap\SitemapExclusions;
use App\Infrastructure\Providers\ModuleServiceProvider;

/**
 * Search visibility: robots.txt, noindex markers, real 404s, redirects, SEO for Acorn routes,
 * sitemap hygiene, and The SEO Framework's settings kept in code. Works without TSF; integrates
 * with it when it is active.
 */
class SeoServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        ApplyRedirects::class,
        HttpsRedirect::class,
        MissingPathNotFound::class,
        SiteRobotsTxt::class,
        RobotsMeta::class,
        RouteSeo::class,
        SitemapExclusions::class,
        RedirectsScreen::class,
    ];

    protected array $commands = [
        ApplySeoSettingsCommand::class,
    ];

    protected array $deployTasks = [
        ApplySeoSettings::class,
    ];

    protected ?string $migrations = __DIR__.'/Database/Migrations';

    public function register(): void
    {
        parent::register();

        // One compiled lookup per request, shared by the redirect hook, the manager and the
        // sitemap exclusions.
        $this->app->singleton(RedirectStore::class);
    }

    public function boot(): void
    {
        parent::boot();

        // Tools → Redirects renders view('seo::admin.redirects').
        $this->loadViewsFrom(__DIR__.'/resources/views', 'seo');
    }
}

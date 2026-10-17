<?php

namespace App\Domains\Commerce;

use App\Domains\Commerce\Contracts\CookieJar;
use App\Domains\Commerce\Hooks\RemoveTemplateHooks;
use App\Domains\Commerce\Hooks\WooCommerceSetup;
use App\Domains\Commerce\Services\FavoritesService;
use App\Domains\Commerce\Support\PhpCookieJar;
use App\Infrastructure\Providers\ModuleServiceProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;

/**
 * WooCommerce integration. Optional: only registered when WooCommerce is active, so a site
 * without a shop carries none of this. Enable it with `composer require wpackagist-plugin/woocommerce`
 * and activating the plugin.
 *
 * Blade template overrides live in resources/views/woocommerce and are wired up by
 * generoi/sage-woocommerce, which stays inert when WooCommerce is absent.
 */
class CommerceServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        WooCommerceSetup::class,
        RemoveTemplateHooks::class,
    ];

    public static function enabled(): bool
    {
        return class_exists('WooCommerce');
    }

    public function register(): void
    {
        parent::register();

        $this->app->bindIf(CookieJar::class, PhpCookieJar::class);
        $this->app->scoped(FavoritesService::class);

        // The favorites cookie carries its own signature and is read on WordPress-rendered pages
        // where Laravel's cookie encryption never runs, so Livewire requests must leave it raw.
        EncryptCookies::except((string) config('commerce.favorites.cookie', 'alma_favorites'));
    }
}

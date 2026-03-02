<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(\App\Dashboard\WidgetManager::class, function () {
            return new \App\Dashboard\WidgetManager();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $router = $this->app['router'];
        $router->aliasMiddleware('auth', \App\Http\Middleware\Authenticate::class);
        $router->aliasMiddleware('guest', \App\Http\Middleware\RedirectIfAuthenticated::class);
        $router->aliasMiddleware('two-factor', \App\Http\Middleware\EnsureTwoFactorVerified::class);



        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag);
    }
}

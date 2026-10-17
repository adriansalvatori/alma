<?php

namespace App\Domains\Account;

use App\Domains\Account\Http\Middleware\Authenticate;
use App\Domains\Account\Http\Middleware\EnsureTwoFactorVerified;
use App\Domains\Account\Http\Middleware\RedirectIfAuthenticated;
use App\Infrastructure\Providers\ModuleServiceProvider;

class AccountServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $router = $this->app['router'];
        $router->aliasMiddleware('auth', Authenticate::class);
        $router->aliasMiddleware('guest', RedirectIfAuthenticated::class);
        $router->aliasMiddleware('two-factor', EnsureTwoFactorVerified::class);
    }
}

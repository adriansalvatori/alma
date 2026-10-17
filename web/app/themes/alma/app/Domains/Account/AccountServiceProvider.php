<?php

namespace App\Domains\Account;

use App\Domains\Account\Hooks\TwoFactorGate;
use App\Domains\Account\Support\Cookies;
use App\Infrastructure\Providers\ModuleServiceProvider;

/**
 * Front-end accounts on top of WordPress users: login, registration, two-factor authentication
 * and a small account area. WordPress stays the identity provider; see docs/account.md.
 */
class AccountServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        TwoFactorGate::class,
    ];

    protected ?string $webRoutes = __DIR__.'/routes/web.php';

    public function register(): void
    {
        parent::register();

        // One instance per request: the gate's remember flag and the cookie log must be shared by
        // every class that touches them.
        $this->app->singleton(Cookies::class);
        $this->app->singleton(TwoFactorGate::class);
    }
}

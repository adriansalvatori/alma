<?php

use App\Domains\Account\Http\Controllers\Auth\LoginController;
use App\Domains\Account\Http\Controllers\Auth\RegisterController;
use App\Domains\Account\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Domains\Account\Http\Controllers\DashboardController;
use App\Domains\Account\Http\Controllers\SettingsController;
use App\Domains\Account\Http\Middleware\Authenticate;
use App\Domains\Account\Http\Middleware\NoIndex;
use App\Domains\Account\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

/*
| Account routes. Middleware is referenced by class rather than alias: Laravel's default aliases
| ("auth", "guest") point at its own guards, which know nothing about WordPress users.
|
| Logging out is WordPress' own nonce-protected flow: link to wp_logout_url(home_url('/')).
*/

Route::middleware(NoIndex::class)->group(function () {
    Route::middleware(RedirectIfAuthenticated::class)->group(function () {
        Route::get('/login', [LoginController::class, 'show'])->name('login');
        Route::get('/register', [RegisterController::class, 'show'])->name('register');

        // No auth middleware: the user is not logged in until this succeeds. Access is controlled
        // by the pending challenge cookie instead (see PendingChallenge).
        Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    });

    Route::middleware(Authenticate::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
        Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    });
});

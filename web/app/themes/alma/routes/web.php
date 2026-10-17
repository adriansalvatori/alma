<?php

use App\Domains\Account\Http\Controllers\Auth\LoginController;
use App\Domains\Account\Http\Controllers\Auth\RegisterController;
use App\Domains\Account\Http\Controllers\DashboardController;
use App\Domains\Account\Http\Controllers\SettingsController;
use App\Domains\Account\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Acorn matches these on parse_request, before WordPress resolves a page, so
| a route here wins over a page with the same slug. Use controllers, not
| closures, so the routes stay cacheable.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
});

Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});

Route::middleware('auth')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

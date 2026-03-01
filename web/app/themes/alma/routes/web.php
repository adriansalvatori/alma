<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
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

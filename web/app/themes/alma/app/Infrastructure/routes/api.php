<?php

use App\Infrastructure\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Core API routes (served under /api with the "api" middleware group)
|--------------------------------------------------------------------------
*/

Route::get('/health', [HealthController::class, 'live'])->name('health');
Route::get('/health/checks', [HealthController::class, 'checks'])->name('health.checks');

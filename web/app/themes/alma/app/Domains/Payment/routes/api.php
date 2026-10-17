<?php

use App\Domains\Payment\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Served under /api with the stateless "api" group: no session, no CSRF. Each gateway verifies
// its own signature, so the endpoint is safe to expose.
Route::post('/payments/webhook/{gateway}', WebhookController::class)
    ->where('gateway', '[a-z0-9_-]+')
    ->name('payments.webhook');

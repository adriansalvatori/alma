<?php

use App\Domains\Payment\Gateways\FakeGateway;
use App\Domains\Payment\Http\Controllers\FakeCheckoutController;
use Illuminate\Support\Facades\Route;

// The fake gateway's checkout page. It does not exist in production at all.
if (FakeGateway::available()) {
    Route::middleware('signed')->group(function () {
        Route::get('/payments/fake/{reference}', [FakeCheckoutController::class, 'show'])->name('payments.fake.show');
        Route::post('/payments/fake/{reference}', [FakeCheckoutController::class, 'confirm'])->name('payments.fake.confirm');
    });
}

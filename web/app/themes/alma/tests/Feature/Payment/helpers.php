<?php

use App\Domains\Payment\Actions\StartCheckoutAction;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\Money;
use App\Domains\Payment\Data\StartedCheckout;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Gateways\FakeGateway;
use App\Domains\Payment\PaymentServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

/**
 * The Payment module is off by default (PAYMENTS_ENABLED), so tests switch it on, register it
 * and migrate its tables.
 */
function enablePayments(): void
{
    config(['payments.enabled' => true]);
    app()->register(PaymentServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();
    Artisan::call('migrate');
}

function startFakeCheckout(int $minor = 2500, string $currency = 'USD'): StartedCheckout
{
    return app(StartCheckoutAction::class)->execute(new CheckoutRequest(
        amount: Money::of($minor, $currency),
        description: 'Deposit',
        customerEmail: 'buyer@example.test',
        successUrl: 'https://example.test/thanks/',
        cancelUrl: 'https://example.test/checkout/',
    ));
}

/**
 * POST a fake-gateway webhook through the real endpoint.
 *
 * @param  array<string, mixed>  $payload
 */
function sendFakeWebhook(array $payload, ?string $signature = null): TestResponse
{
    /** @var FakeGateway $gateway */
    $gateway = app(GatewayManager::class)->gateway('fake');
    $signed = $gateway->signedWebhook($payload);

    return test()->call('POST', '/api/payments/webhook/fake', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ALMA_FAKE_SIGNATURE' => $signature ?? $signed['signature'],
    ], content: $signed['body']);
}

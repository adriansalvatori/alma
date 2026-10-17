<?php

use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Events\PaymentFailed;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Gateways\FakeGateway;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\Support\WordPress\WordPress;

require_once __DIR__.'/helpers.php';

beforeEach(fn () => enablePayments());

it('runs a whole checkout end to end without a provider', function () {
    Event::fake([PaymentSucceeded::class]);
    $started = startFakeCheckout(4200);

    $this->get($started->redirectUrl())
        ->assertOk()
        ->assertSee('$42.00')
        ->assertSee('buyer@example.test');

    $confirm = URL::temporarySignedRoute('payments.fake.confirm', now()->addHour(), ['reference' => $started->payment->reference]);

    $this->post($confirm, ['outcome' => 'succeeded'])->assertRedirect('https://example.test/thanks/');

    expect($started->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    Event::assertDispatched(PaymentSucceeded::class);
});

it('sends a declined shopper back to the cancel URL', function () {
    Event::fake([PaymentFailed::class]);
    $started = startFakeCheckout();
    $confirm = URL::temporarySignedRoute('payments.fake.confirm', now()->addHour(), ['reference' => $started->payment->reference]);

    $this->post($confirm, ['outcome' => 'failed'])->assertRedirect('https://example.test/checkout/');

    expect($started->payment->fresh()->status)->toBe(PaymentStatus::Failed);
});

it('refuses the checkout page without a valid signature', function () {
    $started = startFakeCheckout();

    $this->get('/payments/fake/'.$started->payment->reference)->assertForbidden();
    $this->post('/payments/fake/'.$started->payment->reference, ['outcome' => 'succeeded'])->assertForbidden();

    expect($started->payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('is never configured in production', function () {
    WordPress::$environment = 'production';

    expect(FakeGateway::available())->toBeFalse()
        ->and(app(GatewayManager::class)->gateway('fake')->isConfigured())->toBeFalse();
});

<?php

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\CheckoutSession;
use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Events\PaymentFailed;
use App\Domains\Payment\Events\PaymentRefunded;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Models\ProcessedWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Tests\Support\WordPress\WordPress;

require_once __DIR__.'/helpers.php';

beforeEach(fn () => enablePayments());

function webhookFor(string $reference, string $type, string $eventId = 'evt_1', int $amount = 2500): array
{
    return ['id' => $eventId, 'type' => $type, 'reference' => $reference, 'amount' => $amount, 'currency' => 'USD'];
}

it('answers 404 for a gateway that is not in config', function () {
    $this->postJson('/api/payments/webhook/nope', [])->assertNotFound();
});

it('fails closed with 503 when the gateway is not configured', function () {
    WordPress::$environment = 'production';

    sendFakeWebhook(webhookFor('fake_x', 'succeeded'))->assertStatus(503);
});

it('fails closed for a configured-but-incomplete custom gateway', function () {
    config(['payments.gateways.acme' => ['driver' => 'acme']]);
    app(GatewayManager::class)->extend('acme', fn () => new class implements PaymentGateway
    {
        public function key(): string
        {
            return 'acme';
        }

        public function label(): string
        {
            return 'Acme';
        }

        public function isConfigured(): bool
        {
            return false;
        }

        public function createCheckout(CheckoutRequest $request): CheckoutSession
        {
            throw new LogicException('unused');
        }

        public function parseWebhook(Request $request): PaymentEvent
        {
            throw new LogicException('must not be reached');
        }
    });

    $this->postJson('/api/payments/webhook/acme', ['anything' => true])->assertStatus(503);
});

it('answers 403 to a bad signature and changes nothing', function () {
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded'), signature: 't='.time().',v1=forged')
        ->assertForbidden();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and(ProcessedWebhook::query()->count())->toBe(0);
});

it('marks the payment succeeded and dispatches PaymentSucceeded', function () {
    Event::fake([PaymentSucceeded::class]);
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded'))
        ->assertOk()
        ->assertJson(['status' => 'processed']);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->fresh()->paid_at)->not->toBeNull();
    Event::assertDispatched(PaymentSucceeded::class, fn ($event) => $event->payment->is($payment));
});

it('acts once on a replayed event and answers 200 so the provider stops retrying', function () {
    Event::fake([PaymentSucceeded::class]);
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded'))->assertJson(['status' => 'processed']);
    sendFakeWebhook(webhookFor($payment->reference, 'succeeded'))->assertOk()->assertJson(['status' => 'duplicate']);

    Event::assertDispatchedTimes(PaymentSucceeded::class, 1);
    expect(ProcessedWebhook::query()->count())->toBe(1);
});

it('never lets a late failure undo a success', function () {
    Event::fake([PaymentSucceeded::class, PaymentFailed::class]);
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded', 'evt_1'));
    sendFakeWebhook(webhookFor($payment->reference, 'failed', 'evt_2'))->assertJson(['status' => 'ignored']);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    Event::assertNotDispatched(PaymentFailed::class);
});

it('refunds a succeeded payment', function () {
    Event::fake([PaymentSucceeded::class, PaymentRefunded::class]);
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded', 'evt_1'));
    sendFakeWebhook(webhookFor($payment->reference, 'refunded', 'evt_2'))->assertJson(['status' => 'processed']);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Refunded)
        ->and($payment->fresh()->refunded_at)->not->toBeNull();
    Event::assertDispatched(PaymentRefunded::class);
});

it('marks a declined payment failed', function () {
    Event::fake([PaymentFailed::class]);
    $payment = startFakeCheckout()->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'failed'))->assertJson(['status' => 'processed']);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed);
    Event::assertDispatched(PaymentFailed::class);
});

it('does not mark a payment paid when the amount disagrees', function () {
    Event::fake([PaymentSucceeded::class]);
    $payment = startFakeCheckout(2500)->payment;

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded', amount: 100))->assertJson(['status' => 'ignored']);

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending);
    Event::assertNotDispatched(PaymentSucceeded::class);
});

it('records but ignores an event for an unknown reference', function () {
    sendFakeWebhook(webhookFor('fake_unknown', 'succeeded'))->assertOk()->assertJson(['status' => 'ignored']);

    expect(ProcessedWebhook::query()->sole()->payment_id)->toBeNull();
});

it('rolls back when a synchronous listener fails, so the provider\'s retry is processed', function () {
    $payment = startFakeCheckout()->payment;
    $calls = 0;
    Event::listen(PaymentSucceeded::class, function () use (&$calls) {
        if (++$calls === 1) {
            throw new RuntimeException('Downstream failure');
        }
    });

    $this->withoutExceptionHandling();
    expect(fn () => sendFakeWebhook(webhookFor($payment->reference, 'succeeded')))->toThrow(RuntimeException::class);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and(ProcessedWebhook::query()->count())->toBe(0);

    sendFakeWebhook(webhookFor($payment->reference, 'succeeded'))->assertJson(['status' => 'processed']);
    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
});

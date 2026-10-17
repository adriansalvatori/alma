<?php

use App\Domains\Payment\Actions\StartCheckoutAction;
use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\CheckoutSession;
use App\Domains\Payment\Data\Money;
use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Exceptions\GatewayNotConfigured;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Models\Payment;
use Illuminate\Http\Request;
use Tests\Support\WordPress\WordPress;

require_once __DIR__.'/helpers.php';

beforeEach(fn () => enablePayments());

function brokenGateway(bool $configured = true): PaymentGateway
{
    return new class($configured) implements PaymentGateway
    {
        public function __construct(private bool $configured) {}

        public function key(): string
        {
            return 'broken';
        }

        public function label(): string
        {
            return 'Broken';
        }

        public function isConfigured(): bool
        {
            return $this->configured;
        }

        public function createCheckout(CheckoutRequest $request): CheckoutSession
        {
            throw new RuntimeException('Provider is down');
        }

        public function parseWebhook(Request $request): PaymentEvent
        {
            throw new LogicException('unused');
        }
    };
}

it('records a pending payment and returns the gateway\'s redirect', function () {
    $started = startFakeCheckout(2500);

    expect($started->payment->status)->toBe(PaymentStatus::Pending)
        ->and($started->payment->reference)->toStartWith('fake_')
        ->and($started->payment->money()->equals(Money::of(2500, 'USD')))->toBeTrue()
        ->and($started->redirectUrl())->toContain('/payments/fake/')
        ->and(Payment::query()->count())->toBe(1);
});

it('passes the local payment ID to the gateway as metadata', function () {
    $seen = null;
    config(['payments.gateways.spy' => ['driver' => 'spy']]);
    app(GatewayManager::class)->extend('spy', function () use (&$seen) {
        return new class($seen) implements PaymentGateway
        {
            public function __construct(private mixed &$seen) {}

            public function key(): string
            {
                return 'spy';
            }

            public function label(): string
            {
                return 'Spy';
            }

            public function isConfigured(): bool
            {
                return true;
            }

            public function createCheckout(CheckoutRequest $request): CheckoutSession
            {
                $this->seen = $request->metadata;

                return new CheckoutSession('spy', 'ref-1', 'https://pay.example/ref-1');
            }

            public function parseWebhook(Request $request): PaymentEvent
            {
                throw new LogicException('unused');
            }
        };
    });

    $started = app(StartCheckoutAction::class)->execute(CheckoutRequest::fromArray([
        'amount' => '10.00',
        'currency' => 'EUR',
        'description' => 'Booking fee',
        'customer_email' => 'a@example.test',
        'metadata' => ['source' => 'landing'],
    ]), 'spy');

    expect($seen)->toBe(['source' => 'landing', 'payment_uuid' => $started->payment->uuid]);
});

it('refuses an unconfigured gateway without creating a payment', function () {
    WordPress::$environment = 'production';

    expect(fn () => startFakeCheckout())->toThrow(GatewayNotConfigured::class)
        ->and(Payment::query()->count())->toBe(0);
});

it('marks the payment failed and rethrows when the provider errors', function () {
    config(['payments.gateways.broken' => ['driver' => 'broken']]);
    app(GatewayManager::class)->extend('broken', fn () => brokenGateway());

    expect(fn () => app(StartCheckoutAction::class)->execute(new CheckoutRequest(
        Money::of(100, 'USD'), 'x', 'a@example.test',
    ), 'broken'))->toThrow(RuntimeException::class, 'Provider is down');

    $payment = Payment::query()->sole();

    expect($payment->status)->toBe(PaymentStatus::Failed)
        ->and($payment->failure_reason)->toBe('Provider is down');
});

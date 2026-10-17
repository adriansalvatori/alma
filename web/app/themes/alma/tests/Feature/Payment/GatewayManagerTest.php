<?php

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\CheckoutSession;
use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Exceptions\UnknownGateway;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Gateways\FakeGateway;
use Illuminate\Http\Request;

require_once __DIR__.'/helpers.php';

beforeEach(fn () => enablePayments());

it('resolves the default gateway from config', function () {
    expect(app(GatewayManager::class)->gateway())->toBeInstanceOf(FakeGateway::class)
        ->and(app(GatewayManager::class)->gateway()->key())->toBe('fake');
});

it('throws for a gateway that is not configured', function () {
    expect(fn () => app(GatewayManager::class)->gateway('nope'))->toThrow(UnknownGateway::class)
        ->and(app(GatewayManager::class)->knows('nope'))->toBeFalse()
        ->and(app(GatewayManager::class)->knows('fake'))->toBeTrue();
});

it('hands a custom driver its own config, so one driver can serve two keys', function () {
    config(['payments.gateways.eu' => ['driver' => 'acme', 'account' => 'eu-1']]);
    config(['payments.gateways.us' => ['driver' => 'acme', 'account' => 'us-1']]);

    app(GatewayManager::class)->extend('acme', fn ($app, array $config) => new class($config) implements PaymentGateway
    {
        public function __construct(public array $config) {}

        public function key(): string
        {
            return $this->config['key'];
        }

        public function label(): string
        {
            return 'Acme '.$this->config['account'];
        }

        public function isConfigured(): bool
        {
            return true;
        }

        public function createCheckout(CheckoutRequest $request): CheckoutSession
        {
            return new CheckoutSession($this->key(), 'ref');
        }

        public function parseWebhook(Request $request): PaymentEvent
        {
            throw new LogicException('unused');
        }
    });

    expect(app(GatewayManager::class)->gateway('eu')->label())->toBe('Acme eu-1')
        ->and(app(GatewayManager::class)->gateway('us')->label())->toBe('Acme us-1')
        ->and(app(GatewayManager::class)->gateway('us')->key())->toBe('us');
});

it('throws for an unregistered driver', function () {
    config(['payments.gateways.mystery' => ['driver' => 'mystery']]);

    expect(fn () => app(GatewayManager::class)->gateway('mystery'))->toThrow(UnknownGateway::class);
});

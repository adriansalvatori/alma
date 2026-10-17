<?php

namespace App\Domains\Payment;

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Exceptions\UnknownGateway;
use App\Domains\Payment\Gateways\FakeGateway;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;

/**
 * Resolves gateways by the key they are configured under in config/payments.php.
 *
 * Laravel's Manager pattern with one difference: each driver receives its own config array, so
 * the same driver can be configured twice (two Stripe accounts, say) under different keys.
 * Register a driver from a service provider:
 *
 *     $this->app->make(GatewayManager::class)->extend('stripe',
 *         fn ($app, array $config) => new StripeGateway($config));
 *
 * @method PaymentGateway driver(string|null $driver = null)
 */
class GatewayManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('payments.default', 'fake');
    }

    public function gateway(?string $key = null): PaymentGateway
    {
        return $this->driver($key);
    }

    /**
     * Whether $key is listed in config/payments.php (configured or not).
     */
    public function knows(string $key): bool
    {
        return is_array($this->config->get("payments.gateways.{$key}"));
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys((array) $this->config->get('payments.gateways', []));
    }

    /**
     * @param  string  $driver  The gateway key in config/payments.php.
     */
    protected function createDriver($driver): PaymentGateway
    {
        $config = $this->config->get("payments.gateways.{$driver}");

        if (! is_array($config)) {
            throw UnknownGateway::named($driver);
        }

        $config['key'] = $driver;
        $type = (string) ($config['driver'] ?? $driver);

        if (isset($this->customCreators[$type])) {
            return $this->customCreators[$type]($this->container, $config);
        }

        $method = 'create'.Str::studly($type).'Driver';

        if (method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw UnknownGateway::driver($type);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function createFakeDriver(array $config): FakeGateway
    {
        return new FakeGateway($config);
    }
}

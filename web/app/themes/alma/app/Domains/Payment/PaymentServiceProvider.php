<?php

namespace App\Domains\Payment;

use App\Domains\Payment\Events\PaymentFailed;
use App\Domains\Payment\Events\PaymentRefunded;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\Hooks\RegisterWooCommerceGateways;
use App\Domains\Payment\Listeners\SyncWooCommerceOrder;
use App\Infrastructure\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Gateway-agnostic payments: a standalone checkout (StartCheckoutAction), a webhook endpoint
 * that fails closed, idempotent event handling, and an optional WooCommerce bridge.
 *
 * Optional: only registered when PAYMENTS_ENABLED=true. Real gateways are added per project
 * (docs/payments.md); the fake gateway makes the whole flow demoable without one.
 */
class PaymentServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        RegisterWooCommerceGateways::class,
    ];

    protected ?string $migrations = __DIR__.'/Database/Migrations';

    protected ?string $webRoutes = __DIR__.'/routes/web.php';

    protected ?string $apiRoutes = __DIR__.'/routes/api.php';

    public static function enabled(): bool
    {
        return (bool) config('payments.enabled', false);
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(GatewayManager::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/resources/views', 'payment');

        if (class_exists('WooCommerce')) {
            foreach ([PaymentSucceeded::class, PaymentFailed::class, PaymentRefunded::class] as $event) {
                Event::listen($event, [SyncWooCommerceOrder::class, 'handle']);
            }
        }
    }
}

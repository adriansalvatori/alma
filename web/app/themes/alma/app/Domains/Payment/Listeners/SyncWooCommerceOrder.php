<?php

namespace App\Domains\Payment\Listeners;

use App\Domains\Payment\Events\PaymentFailed;
use App\Domains\Payment\Events\PaymentRefunded;
use App\Domains\Payment\Events\PaymentSucceeded;

/**
 * Mirror a payment's outcome onto the WooCommerce order it was started for.
 *
 * Synchronous on purpose: it runs inside the webhook's transaction, so if WooCommerce throws,
 * the event is not recorded as handled and the provider's retry gets another chance.
 */
class SyncWooCommerceOrder
{
    public function handle(PaymentSucceeded|PaymentFailed|PaymentRefunded $event): void
    {
        $orderId = (int) ($event->payment->metadata['wc_order_id'] ?? 0);

        if ($orderId === 0 || ! function_exists('wc_get_order') || ! ($order = wc_get_order($orderId))) {
            return;
        }

        match (true) {
            $event instanceof PaymentSucceeded => $order->payment_complete((string) $event->payment->reference),
            $event instanceof PaymentFailed => $order->update_status('failed', __('Payment failed at the provider.', 'alma')),
            $event instanceof PaymentRefunded => $order->update_status('refunded', __('Payment refunded at the provider.', 'alma')),
        };
    }
}

<?php

namespace App\Domains\Payment\Hooks;

use App\Domains\Payment\WooCommerce\WooCommerceGatewayBridge;

/**
 * Add the bridges listed in config('payments.woocommerce.gateways') to WooCommerce's payment
 * methods. Does nothing without WooCommerce.
 */
class RegisterWooCommerceGateways
{
    public function register(): void
    {
        add_filter('woocommerce_payment_gateways', [$this, 'add']);
    }

    /**
     * @param  array<int, mixed>  $methods
     * @return array<int, mixed>
     */
    public function add(array $methods): array
    {
        if (! class_exists('WC_Payment_Gateway')) {
            return $methods;
        }

        foreach ((array) config('payments.woocommerce.gateways', []) as $bridge) {
            if (is_string($bridge) && is_subclass_of($bridge, WooCommerceGatewayBridge::class)) {
                $methods[] = $bridge;
            }
        }

        return $methods;
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | The Payment module only registers when this is true. See docs/payments.md
    | for adding a real gateway.
    |
    */

    'enabled' => (bool) env('PAYMENTS_ENABLED', false),

    // The gateway StartCheckoutAction uses when none is named.
    'default' => env('PAYMENTS_GATEWAY', 'fake'),

    // The currency for amounts given without one.
    'currency' => env('PAYMENTS_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    |
    | Keyed by the name used in the webhook URL (/api/payments/webhook/{key}).
    | "driver" picks the implementation; the rest is passed to it. A gateway
    | whose credentials are missing reports itself unconfigured and its webhook
    | answers 503.
    |
    */

    'gateways' => [

        // No provider behind it. Never available in production.
        'fake' => [
            'driver' => 'fake',
            // Optional: derived from APP_KEY when empty.
            'secret' => env('PAYMENTS_FAKE_SECRET'),
        ],

        // 'wompi' => [
        //     'driver' => 'wompi',
        //     'public_key' => env('WOMPI_PUBLIC_KEY'),
        //     'private_key' => env('WOMPI_PRIVATE_KEY'),
        //     'events_secret' => env('WOMPI_EVENTS_SECRET'),
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | WooCommerce
    |--------------------------------------------------------------------------
    |
    | WooCommerceGatewayBridge subclasses to offer at the WooCommerce checkout
    | (only when the Commerce module is active).
    |
    */

    'woocommerce' => [
        'gateways' => [
            // App\Domains\Payment\WooCommerce\Gateways\WompiWooCommerceGateway::class,
        ],
    ],

];

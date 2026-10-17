<?php

use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\Hooks\RegisterWooCommerceGateways;
use App\Domains\Payment\Listeners\SyncWooCommerceOrder;
use App\Domains\Payment\Models\Payment;

require_once __DIR__.'/helpers.php';

beforeEach(fn () => enablePayments());

it('adds no WooCommerce payment methods when WooCommerce is absent', function () {
    config(['payments.woocommerce.gateways' => ['Some\\Bridge']]);

    expect((new RegisterWooCommerceGateways)->add(['existing']))->toBe(['existing']);
});

it('leaves payments without a WooCommerce order alone', function () {
    $payment = new Payment(['metadata' => []]);

    (new SyncWooCommerceOrder)->handle(new PaymentSucceeded($payment));

    expect(true)->toBeTrue();
});

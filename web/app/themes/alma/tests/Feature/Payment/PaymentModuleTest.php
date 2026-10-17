<?php

use App\Domains\Payment\PaymentServiceProvider;

it('stays off unless PAYMENTS_ENABLED is true', function () {
    expect(PaymentServiceProvider::enabled())->toBeFalse()
        ->and(app()->getLoadedProviders())->not->toHaveKey(PaymentServiceProvider::class);

    config(['payments.enabled' => true]);

    expect(PaymentServiceProvider::enabled())->toBeTrue();
});

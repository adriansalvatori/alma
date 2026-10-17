<?php

namespace App\Domains\Payment\Data;

/**
 * The provider's answer to a checkout request: where to send the shopper (hosted checkout) or
 * the client secret an embedded form needs, plus the provider's reference for the payment.
 */
final readonly class CheckoutSession
{
    public function __construct(
        public string $gateway,
        public string $reference,
        public ?string $redirectUrl = null,
        public ?string $clientSecret = null,
    ) {}
}

<?php

namespace App\Domains\Payment\Data;

use App\Domains\Payment\Models\Payment;

final readonly class StartedCheckout
{
    public function __construct(
        public Payment $payment,
        public CheckoutSession $session,
    ) {}

    public function redirectUrl(): ?string
    {
        return $this->session->redirectUrl;
    }
}

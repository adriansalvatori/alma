<?php

namespace App\Domains\Payment\Contracts;

use App\Domains\Payment\Data\Money;
use App\Domains\Payment\Models\Payment;

/**
 * Optional: a gateway that can issue refunds from the site. The refund itself is confirmed
 * by the provider's webhook, which moves the payment to "refunded".
 */
interface RefundsPayments
{
    /**
     * Request a refund of $amount (the full amount when null). Returns the provider's refund ID.
     */
    public function refund(Payment $payment, ?Money $amount = null): string;
}

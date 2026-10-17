<?php

namespace App\Domains\Payment\Events;

use App\Domains\Payment\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use SerializesModels;

    public function __construct(public Payment $payment) {}
}

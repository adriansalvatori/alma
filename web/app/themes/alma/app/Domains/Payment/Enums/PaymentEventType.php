<?php

namespace App\Domains\Payment\Enums;

enum PaymentEventType: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Pending = 'pending';

    /**
     * The payment status this event moves a payment to, or null when it changes nothing.
     */
    public function status(): ?PaymentStatus
    {
        return match ($this) {
            self::Succeeded => PaymentStatus::Succeeded,
            self::Failed => PaymentStatus::Failed,
            self::Refunded => PaymentStatus::Refunded,
            self::Pending => null,
        };
    }
}

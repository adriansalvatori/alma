<?php

namespace App\Domains\Payment\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Refunded = 'refunded';

    /**
     * The transitions a webhook may make. Anything else is ignored, so a late or replayed
     * "failed" event can never undo a payment that already succeeded.
     */
    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Pending => in_array($to, [self::Succeeded, self::Failed], true),
            // Asynchronous methods (bank transfers, cash vouchers) can fail first and settle later.
            self::Failed => $to === self::Succeeded,
            self::Succeeded => $to === self::Refunded,
            self::Refunded => false,
        };
    }
}

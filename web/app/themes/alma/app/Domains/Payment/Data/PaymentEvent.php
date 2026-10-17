<?php

namespace App\Domains\Payment\Data;

use App\Domains\Payment\Enums\PaymentEventType;

/**
 * A verified webhook from a provider, translated into the module's vocabulary.
 */
final readonly class PaymentEvent
{
    /**
     * @param  string  $eventId  The provider's unique event ID, used for idempotency.
     * @param  string  $reference  The provider's payment reference (matches Payment::$reference).
     * @param  array<string, mixed>  $payload  The decoded body, kept for the audit trail.
     */
    public function __construct(
        public PaymentEventType $type,
        public string $eventId,
        public string $reference,
        public ?Money $amount = null,
        public array $payload = [],
    ) {}
}

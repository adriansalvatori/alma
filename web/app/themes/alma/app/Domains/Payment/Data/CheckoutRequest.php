<?php

namespace App\Domains\Payment\Data;

/**
 * What to charge and where to send the shopper afterwards. Gateway-agnostic: each gateway maps
 * it onto its own API.
 */
final readonly class CheckoutRequest
{
    /**
     * @param  array<string, scalar|null>  $metadata  Echoed back by most providers; keep it small.
     * @param  string|null  $leadUuid  Links the payment to a Lead (soft link; the Lead module is optional).
     */
    public function __construct(
        public Money $amount,
        public string $description,
        public string $customerEmail,
        public ?string $customerName = null,
        public array $metadata = [],
        public ?string $successUrl = null,
        public ?string $cancelUrl = null,
        public ?string $leadUuid = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data  amount_minor or amount (decimal string), currency, description, ...
     */
    public static function fromArray(array $data): self
    {
        $currency = (string) ($data['currency'] ?? config('payments.currency', 'USD'));

        return new self(
            amount: isset($data['amount_minor'])
                ? Money::of((int) $data['amount_minor'], $currency)
                : Money::fromDecimal((string) ($data['amount'] ?? '0'), $currency),
            description: (string) ($data['description'] ?? ''),
            customerEmail: (string) ($data['customer_email'] ?? ''),
            customerName: isset($data['customer_name']) ? (string) $data['customer_name'] : null,
            metadata: (array) ($data['metadata'] ?? []),
            successUrl: $data['success_url'] ?? null,
            cancelUrl: $data['cancel_url'] ?? null,
            leadUuid: $data['lead_uuid'] ?? null,
        );
    }

    /**
     * @param  array<string, scalar|null>  $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return new self(
            $this->amount,
            $this->description,
            $this->customerEmail,
            $this->customerName,
            [...$this->metadata, ...$metadata],
            $this->successUrl,
            $this->cancelUrl,
            $this->leadUuid,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'amount_minor' => $this->amount->minor,
            'currency' => $this->amount->currency,
            'description' => $this->description,
            'customer_email' => $this->customerEmail,
            'customer_name' => $this->customerName,
            'metadata' => $this->metadata,
            'success_url' => $this->successUrl,
            'cancel_url' => $this->cancelUrl,
            'lead_uuid' => $this->leadUuid,
        ];
    }
}

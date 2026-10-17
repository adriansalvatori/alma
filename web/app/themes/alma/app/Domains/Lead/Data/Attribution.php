<?php

namespace App\Domains\Lead\Data;

/**
 * First and last touch. The first touch is kept for as long as the cookie lives (what brought the
 * visitor to the site at all); the last touch is replaced by every new one (what brought them back
 * before they converted).
 */
readonly class Attribution
{
    public function __construct(
        public ?Touch $first = null,
        public ?Touch $last = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            first: is_array($data['first'] ?? null) ? Touch::fromArray($data['first']) : null,
            last: is_array($data['last'] ?? null) ? Touch::fromArray($data['last']) : null,
        );
    }

    /**
     * @return array{first: array<string, string|null>|null, last: array<string, string|null>|null}
     */
    public function toArray(): array
    {
        return [
            'first' => $this->first?->toArray(),
            'last' => $this->last?->toArray(),
        ];
    }

    public function with(Touch $touch): self
    {
        return new self(first: $this->first ?? $touch, last: $touch);
    }

    public function isEmpty(): bool
    {
        return $this->first === null && $this->last === null;
    }
}

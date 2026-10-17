<?php

namespace App\Domains\Lead\Data;

/**
 * A submitted lead, before it is stored. Built by the form (or any other entry point: an API
 * route, an import) and handed to CaptureLeadAction.
 */
readonly class LeadData
{
    /**
     * @param  array<string, mixed>  $fields  Extra form fields, stored as JSON.
     */
    public function __construct(
        public string $email,
        public string $name = '',
        public ?string $phone = null,
        public ?string $company = null,
        public ?string $message = null,
        public array $fields = [],
        public string $formKey = 'default',
        public bool $consent = false,
        public ?Attribution $attribution = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: (string) ($data['email'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            phone: isset($data['phone']) && $data['phone'] !== '' ? (string) $data['phone'] : null,
            company: isset($data['company']) && $data['company'] !== '' ? (string) $data['company'] : null,
            message: isset($data['message']) && $data['message'] !== '' ? (string) $data['message'] : null,
            fields: is_array($data['fields'] ?? null) ? $data['fields'] : [],
            formKey: (string) ($data['form_key'] ?? 'default'),
            consent: (bool) ($data['consent'] ?? false),
            attribution: match (true) {
                ($data['attribution'] ?? null) instanceof Attribution => $data['attribution'],
                is_array($data['attribution'] ?? null) => Attribution::fromArray($data['attribution']),
                default => null,
            },
            ip: isset($data['ip']) ? (string) $data['ip'] : null,
            userAgent: isset($data['user_agent']) ? (string) $data['user_agent'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'company' => $this->company,
            'message' => $this->message,
            'fields' => $this->fields,
            'form_key' => $this->formKey,
            'consent' => $this->consent,
            'attribution' => $this->attribution?->toArray(),
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
        ];
    }
}

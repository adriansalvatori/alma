<?php

namespace App\Domains\Account\Data;

/**
 * A password-verified login waiting for its second factor.
 */
readonly class PendingLogin
{
    public function __construct(
        public string $token,
        public int $userId,
        public bool $remember,
        public int $attempts,
        public ?string $redirectTo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(string $token, array $data): self
    {
        return new self(
            token: $token,
            userId: (int) ($data['user_id'] ?? 0),
            remember: (bool) ($data['remember'] ?? false),
            attempts: (int) ($data['attempts'] ?? 0),
            redirectTo: isset($data['redirect_to']) && is_string($data['redirect_to']) ? $data['redirect_to'] : null,
        );
    }

    /**
     * @return array{user_id: int, remember: bool, attempts: int, redirect_to: ?string}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'remember' => $this->remember,
            'attempts' => $this->attempts,
            'redirect_to' => $this->redirectTo,
        ];
    }
}

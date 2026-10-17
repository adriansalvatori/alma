<?php

namespace App\Domains\Ai\Safety;

/**
 * Stateless confirmation for destructive abilities.
 *
 * A token is "<expiry>.<hmac>", the HMAC covering the ability, the user and the exact input. It
 * only confirms the call it was issued for: the same agent cannot reuse it on other input, and a
 * different user cannot use it at all. Nothing is stored, so a client that reconnects between the
 * preview and the confirmation still works.
 */
class ConfirmationToken
{
    public function ttl(): int
    {
        return (int) config('ai.confirmation_ttl', 600);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function issue(string $ability, int $userId, array $input, ?int $now = null): string
    {
        $expires = ($now ?? time()) + $this->ttl();

        return $expires.'.'.$this->signature($expires, $ability, $userId, $input);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function verify(string $token, string $ability, int $userId, array $input, ?int $now = null): bool
    {
        if (! preg_match('/^(\d+)\.([a-f0-9]{64})$/', $token, $parts)) {
            return false;
        }

        $expires = (int) $parts[1];

        if ($expires < ($now ?? time())) {
            return false;
        }

        return hash_equals($this->signature($expires, $ability, $userId, $input), $parts[2]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function signature(int $expires, string $ability, int $userId, array $input): string
    {
        $payload = implode('|', [$expires, $ability, $userId, json_encode(self::canonical($input))]);

        return hash_hmac('sha256', $payload, wp_salt('auth'));
    }

    /**
     * Sort keys recursively, so {"a":1,"b":2} and {"b":2,"a":1} confirm the same call.
     */
    private static function canonical(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(self::canonical(...), $value);
    }
}

<?php

namespace App\Domains\Account\Services;

/**
 * Single-use codes for when the authenticator app is gone.
 *
 * Only keyed hashes are stored. The codes carry ~60 bits of randomness each, so an HMAC is as good
 * as a slow password hash here, and checking eight of them stays instant.
 */
class RecoveryCodes
{
    public const META = 'alma_2fa_recovery_codes';

    public const COUNT = 8;

    /**
     * Replace the user's codes with a fresh set and return them in plain text, once.
     *
     * @return list<string>
     */
    public function generate(int $userId): array
    {
        $codes = [];

        for ($i = 0; $i < self::COUNT; $i++) {
            $raw = strtolower(bin2hex(random_bytes(5)));
            $codes[] = substr($raw, 0, 5).'-'.substr($raw, 5);
        }

        update_user_meta($userId, self::META, array_map(fn (string $code) => $this->hash($code), $codes));

        return $codes;
    }

    /**
     * Use up a code. True when it matched one that had not been used yet.
     */
    public function consume(int $userId, string $code): bool
    {
        $hash = $this->hash($code);
        $stored = $this->stored($userId);

        foreach ($stored as $i => $candidate) {
            if (hash_equals($candidate, $hash)) {
                unset($stored[$i]);
                update_user_meta($userId, self::META, array_values($stored));

                return true;
            }
        }

        return false;
    }

    public function remaining(int $userId): int
    {
        return count($this->stored($userId));
    }

    public function clear(int $userId): void
    {
        delete_user_meta($userId, self::META);
    }

    /**
     * @return list<string>
     */
    protected function stored(int $userId): array
    {
        $stored = get_user_meta($userId, self::META, true);

        return is_array($stored) ? array_values(array_filter($stored, 'is_string')) : [];
    }

    protected function hash(string $code): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', $code));

        return hash_hmac('sha256', $normalized, wp_salt('auth'));
    }
}

<?php

namespace App\Domains\Account\Services;

use App\Domains\Account\Data\PendingLogin;
use App\Domains\Account\Support\Cookies;
use WP_User;

/**
 * The state between "the password was right" and "the second factor was right".
 *
 * The browser holds only a random token (HttpOnly cookie). The user id, the remember flag and
 * the attempt count live in a transient keyed by a hash of that token, so a stolen database row
 * cannot be replayed as a cookie and the browser cannot edit whose login it is completing.
 */
class PendingChallenge
{
    public const COOKIE = 'alma_2fa';

    public const TTL = 600;

    public const MAX_ATTEMPTS = 5;

    public function __construct(protected Cookies $cookies) {}

    public function start(WP_User $user, bool $remember, ?string $redirectTo = null): PendingLogin
    {
        $this->clear();

        $pending = new PendingLogin(
            token: bin2hex(random_bytes(32)),
            userId: $user->ID,
            remember: $remember,
            attempts: 0,
            redirectTo: $redirectTo,
        );

        set_transient($this->key($pending->token), $pending->toArray(), self::TTL);
        $this->cookies->set(self::COOKIE, $pending->token, time() + self::TTL);

        return $pending;
    }

    /**
     * The pending login for this browser, or null when there is none, it expired, or it has used
     * up its attempts.
     */
    public function current(): ?PendingLogin
    {
        $token = $this->cookies->get(self::COOKIE);

        if ($token === null || ! ctype_xdigit($token)) {
            return null;
        }

        $data = get_transient($this->key($token));

        if (! is_array($data)) {
            return null;
        }

        $pending = PendingLogin::fromArray($token, $data);

        return $pending->userId > 0 && $pending->attempts < self::MAX_ATTEMPTS ? $pending : null;
    }

    /**
     * Count a wrong code. Returns the attempts left; at zero the challenge is gone and the user has
     * to enter their password again.
     */
    public function recordFailure(PendingLogin $pending): int
    {
        $attempts = $pending->attempts + 1;
        $remaining = max(0, self::MAX_ATTEMPTS - $attempts);

        if ($remaining === 0) {
            $this->clear();

            return 0;
        }

        set_transient($this->key($pending->token), ['attempts' => $attempts] + $pending->toArray(), self::TTL);

        return $remaining;
    }

    public function clear(): void
    {
        if ($token = $this->cookies->get(self::COOKIE)) {
            delete_transient($this->key($token));
            $this->cookies->forget(self::COOKIE);
        }
    }

    protected function key(string $token): string
    {
        return 'alma_2fa_'.hash('sha256', $token);
    }
}

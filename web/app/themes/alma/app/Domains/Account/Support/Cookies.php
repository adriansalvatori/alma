<?php

namespace App\Domains\Account\Support;

/**
 * Raw (unencrypted) cookies, readable on every request type.
 *
 * The two-factor challenge has to survive a hop from wp-login.php, which never passes through
 * Laravel's middleware, to an Acorn route. Laravel's encrypted cookie jar is not available on the
 * first, so the challenge cookie is a plain random token and the state lives server-side.
 *
 * Writes also update $_COOKIE, so a read later in the same request sees the new value. Every
 * write is recorded in $sent, which is how the tests check the cookie attributes.
 */
class Cookies
{
    /** @var list<array{name: string, value: string, expires: int, secure: bool, httponly: bool, samesite: string}> */
    public array $sent = [];

    public function get(string $name): ?string
    {
        $value = $_COOKIE[$name] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function set(string $name, string $value, int $expires): void
    {
        $options = [
            'expires' => $expires,
            'path' => defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/',
            'domain' => defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if (! headers_sent()) {
            setcookie($name, $value, $options);
        }

        $this->sent[] = ['name' => $name, 'value' => $value] + array_intersect_key($options, array_flip(['expires', 'secure', 'httponly', 'samesite']));

        if ($value === '') {
            unset($_COOKIE[$name]);
        } else {
            $_COOKIE[$name] = $value;
        }
    }

    public function forget(string $name): void
    {
        $this->set($name, '', time() - 3600);
    }
}

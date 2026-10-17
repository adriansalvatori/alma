<?php

namespace App\Domains\Commerce\Support;

use App\Domains\Commerce\Contracts\CookieJar;

/**
 * Reads $_COOKIE and writes with setcookie().
 *
 * Laravel's Cookie facade only works on requests Acorn routes itself (it relies on the
 * EncryptCookies and AddQueuedCookiesToResponse middleware). Favorites are read on ordinary
 * WordPress pages too, so the cookie is written raw and protected by its own signature instead
 * (see FavoritesService), and excluded from EncryptCookies by the Commerce provider.
 */
class PhpCookieJar implements CookieJar
{
    public function get(string $name): ?string
    {
        $value = $_COOKIE[$name] ?? null;

        return is_string($value) ? wp_unslash($value) : null;
    }

    public function set(string $name, string $value, int $minutes): void
    {
        $_COOKIE[$name] = $value;

        if (headers_sent()) {
            return;
        }

        setcookie($name, $value, [
            'expires' => time() + ($minutes * 60),
            'path' => defined('COOKIEPATH') ? COOKIEPATH : '/',
            'domain' => defined('COOKIE_DOMAIN') ? (string) COOKIE_DOMAIN : '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function forget(string $name): void
    {
        unset($_COOKIE[$name]);

        if (! headers_sent()) {
            setcookie($name, '', ['expires' => time() - 3600, 'path' => defined('COOKIEPATH') ? COOKIEPATH : '/']);
        }
    }
}

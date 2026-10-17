<?php

namespace App\Domains\Commerce\Contracts;

/**
 * Raw cookie access, so services that keep state in a cookie can be tested without sending
 * headers. The production implementation writes with setcookie(), because a WordPress-rendered
 * page never passes through Laravel's cookie middleware.
 */
interface CookieJar
{
    public function get(string $name): ?string;

    public function set(string $name, string $value, int $minutes): void;

    public function forget(string $name): void;
}

<?php

namespace App\Domains\Payment\Exceptions;

use RuntimeException;

class GatewayNotConfigured extends RuntimeException
{
    public static function named(string $key): self
    {
        return new self("The \"{$key}\" payment gateway is missing credentials. Check its keys in .env.");
    }
}

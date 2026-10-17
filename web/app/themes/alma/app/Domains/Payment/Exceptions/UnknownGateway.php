<?php

namespace App\Domains\Payment\Exceptions;

use InvalidArgumentException;

class UnknownGateway extends InvalidArgumentException
{
    public static function named(string $key): self
    {
        return new self("No payment gateway is configured under \"{$key}\" in config/payments.php.");
    }

    public static function driver(string $driver): self
    {
        return new self("No payment gateway driver \"{$driver}\" is registered. Register it with GatewayManager::extend().");
    }
}

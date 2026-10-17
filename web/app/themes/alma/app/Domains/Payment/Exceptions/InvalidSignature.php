<?php

namespace App\Domains\Payment\Exceptions;

use RuntimeException;

class InvalidSignature extends RuntimeException
{
    public static function for(string $gateway): self
    {
        return new self("The {$gateway} webhook signature is missing, invalid or expired.");
    }
}

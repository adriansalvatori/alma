<?php

namespace App\Domains\Commerce;

use App\Infrastructure\Providers\ModuleServiceProvider;

/**
 * WooCommerce integration. Optional: only registered when WooCommerce is active.
 */
class CommerceServiceProvider extends ModuleServiceProvider
{
    public static function enabled(): bool
    {
        return class_exists('WooCommerce');
    }
}

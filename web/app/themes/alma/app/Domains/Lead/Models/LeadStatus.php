<?php

namespace App\Domains\Lead\Models;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Won = 'won';
    case Lost = 'lost';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New', 'alma'),
            self::Contacted => __('Contacted', 'alma'),
            self::Qualified => __('Qualified', 'alma'),
            self::Won => __('Won', 'alma'),
            self::Lost => __('Lost', 'alma'),
            self::Spam => __('Spam', 'alma'),
        };
    }

    /**
     * The admin badge tone (<x-admin::badge>): new leads stand out, closed ones recede.
     */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'brand',
            self::Contacted => 'info',
            self::Qualified => 'warning',
            self::Won => 'success',
            self::Lost => 'neutral',
            self::Spam => 'danger',
        };
    }
}

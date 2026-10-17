<?php

namespace App\Support;

use Illuminate\View\ComponentAttributeBag;

/**
 * Livewire's wire:navigate (SPA-style page transitions), as an opt-in.
 *
 * Off by default: it changes how every link behaves (scripts run once, analytics must listen for
 * livewire:navigated). Turn it on with WIRE_NAVIGATE=true. Plain links use the @navigate Blade
 * directive; Flux components take :attributes="\App\Support\Navigate::attributes()".
 */
class Navigate
{
    public static function enabled(): bool
    {
        return (bool) config('content.navigate', false);
    }

    /**
     * The attribute for a plain <a>, or nothing.
     */
    public static function attribute(): string
    {
        return self::enabled() ? 'wire:navigate' : '';
    }

    /**
     * The attribute for a Blade component, or an empty bag.
     */
    public static function attributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag(self::enabled() ? ['wire:navigate' => true] : []);
    }
}

<?php

namespace App\Blocks;

use App\Blocks\Concerns\FieldDefaults;
use Log1x\AcfComposer\Block as AcfBlock;

/**
 * Base class for the theme's ACF blocks. Abstract, so ACF Composer does not register it.
 *
 * The block authoring contract (docs/blocks.md), enforced by tests/Feature/Conventions:
 *
 * - DEFAULTS holds every piece of fallback copy, and each field's default_value points at it.
 * - with() reads fields through value() / rows(), never `get_field() ?: ...`.
 * - Repeaters that render preset rows use HasRepeaterDefaults.
 * - Links are text fields (ACF's URL field rejects /paths and #anchors).
 * - The field group is named "{slug_with_underscores}_block", so BlockMarkup can address fields.
 * - $example holds preview data, and the view's first line is a Blade comment saying what the
 *   block renders (the block inventory reads it).
 */
abstract class Block extends AcfBlock
{
    use FieldDefaults;

    /**
     * Fallback copy, keyed by field name.
     *
     * @var array<string, mixed>
     */
    public const DEFAULTS = [];

    /**
     * Heroicons offered by icon fields (Flux renders them by name).
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'bolt' => 'Bolt',
        'rocket-launch' => 'Rocket',
        'shield-check' => 'Shield',
        'chart-bar' => 'Chart',
        'sparkles' => 'Sparkles',
        'device-phone-mobile' => 'Phone',
        'globe-alt' => 'Globe',
        'cursor-arrow-rays' => 'Cursor',
        'chat-bubble-left-right' => 'Conversation',
        'cog-6-tooth' => 'Settings',
        'clock' => 'Clock',
        'check-badge' => 'Badge',
    ];

    public $category = 'alma';

    public $mode = 'preview';

    /**
     * Full-bleed sections with an optional anchor. Fields are edited in the sidebar, so the
     * edit/preview mode switch only gets in the way.
     *
     * @var array<string, mixed>
     */
    public $supports = [
        'align' => ['full'],
        'anchor' => true,
        'mode' => false,
    ];

    public $align = 'full';

    public $styles = [];
}

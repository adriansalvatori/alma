<?php

namespace App\Blocks\Concerns;

/**
 * Read a block's fields with its presets as the fallback.
 *
 * The block's DEFAULTS constant is the only source of fallback copy: the field definitions point
 * their default_value at it (so the sidebar shows what the block renders) and with() falls back
 * to it through value(). tests/Feature/Conventions/BlockFieldDefaultsTest keeps the two in step.
 */
trait FieldDefaults
{
    /**
     * A field's value, or its preset when the field is unset or emptied.
     *
     * The check is explicit on purpose: `?:` also swallows 0 and '0', which is how a 0% progress
     * bar once rendered at 51% on Remote Leverage.
     */
    protected function value(string $key): mixed
    {
        $value = function_exists('get_field') ? get_field($key) : null;

        return $value === null || $value === '' || $value === false
            ? (static::DEFAULTS[$key] ?? null)
            : $value;
    }

    /**
     * Repeater rows, or the block's preset rows when nothing was ever stored.
     *
     * Only null (never saved) falls back. A repeater an editor emptied comes back false or [] and
     * stays empty, so removing every row actually removes them.
     *
     * @return list<array<string, mixed>>
     */
    protected function rows(string $key): array
    {
        $rows = function_exists('get_field') ? get_field($key) : null;

        if ($rows === null) {
            return method_exists(static::class, 'repeaterDefaults')
                ? array_values(static::repeaterDefaults()[$key] ?? [])
                : [];
        }

        return is_array($rows) ? array_values($rows) : [];
    }
}

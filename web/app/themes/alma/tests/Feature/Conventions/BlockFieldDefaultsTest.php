<?php

/*
 * A block's fallback copy is its field default: the sidebar must show what the block renders.
 *
 * DEFAULTS is the only source of fallback copy. Each entry must equal its field's default_value
 * (or placeholder), every top-level field with a default must be in DEFAULTS, and a choice
 * field's default must be one of its choices (acf-builder silently drops plain numeric keys,
 * so a default of '3' would point at nothing).
 */

require_once __DIR__.'/Support.php';

function convention_normalise(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? '1' : '0',
        is_array($value) => json_encode($value),
        default => (string) $value,
    };
}

it('keeps DEFAULTS and the field defaults identical', function () {
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        $defaults = defined($block::class.'::DEFAULTS') ? $block::DEFAULTS : [];
        $fields = convention_fields($block);

        foreach ($defaults as $name => $value) {
            if (! isset($fields[$name])) {
                $problems[] = "{$slug}: DEFAULTS[{$name}] has no field";

                continue;
            }

            $field = $fields[$name];
            $fieldDefault = $field['default_value'] ?? $field['placeholder'] ?? null;

            if (convention_normalise($fieldDefault) !== convention_normalise($value)) {
                $problems[] = "{$slug}.{$name}: default_value [".convention_normalise($fieldDefault).'] differs from DEFAULTS ['.convention_normalise($value).']';
            }
        }

        foreach ($fields as $name => $field) {
            $default = $field['default_value'] ?? null;

            if ($default !== null && $default !== '' && $default !== false && ! array_key_exists($name, $defaults)) {
                $problems[] = "{$slug}.{$name}: has a default_value but no DEFAULTS entry";
            }
        }
    }

    expect($problems)->toBe([]);
});

it('gives every choice field a default that is one of its choices', function () {
    $problems = [];

    foreach (convention_blocks() as $slug => $block) {
        $walk = function (array $fields, string $prefix) use (&$walk, &$problems, $slug) {
            foreach ($fields as $field) {
                $type = $field['type'] ?? '';

                if (in_array($type, ['select', 'radio', 'button_group'], true) && isset($field['default_value'])) {
                    $choices = array_map('strval', array_keys((array) ($field['choices'] ?? [])));

                    foreach ((array) $field['default_value'] as $default) {
                        if (! in_array((string) $default, $choices, true)) {
                            $problems[] = "{$slug}.{$prefix}{$field['name']}: default [{$default}] is not a choice (".implode(', ', $choices).')';
                        }
                    }
                }

                $walk($field['sub_fields'] ?? [], $prefix.$field['name'].'.');
            }
        };

        $walk($block->fields()['fields'] ?? [], '');
    }

    expect($problems)->toBe([]);
});

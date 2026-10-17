<?php

namespace App\Blocks\Concerns;

/**
 * A block's sidebar shows the rows it renders for an empty repeater.
 *
 * ACF has no default value for a repeater, so a block that renders preset rows while its repeater
 * is empty (three feature cards, four FAQs) showed an empty sidebar, and changing one card meant
 * typing every card back in. A block using this declares those rows once, in repeaterDefaults().
 * Its with() falls back to them (FieldDefaults::rows()), and the form loads them for a repeater
 * nothing is stored in, the way it shows a text field's default_value.
 *
 * The rows are hooked to the repeater's own field KEY. Remote Leverage first matched by name and
 * every repeater called `items` received another block's presets.
 *
 * Only the form loads them. On the page, a block that stores nothing for a repeater never reaches
 * this hook, and the block's own fallback renders. tests/Feature/Conventions/BlockRepeaterDefaultsTest
 * pins that the rows the form loads are the rows the block renders.
 */
trait HasRepeaterDefaults
{
    /**
     * The rows each repeater renders while it is empty, keyed by repeater name, in the shape
     * get_field() returns them (sub-field name => value).
     *
     * @return array<string, list<array<string, mixed>>>
     */
    abstract public static function repeaterDefaults(): array;

    /**
     * Hook the presets to each repeater's field key. ACF Composer calls this after it has
     * registered the block's field group.
     */
    public function afterRegister(): void
    {
        $class = static::class;

        foreach ($this->fields['fields'] ?? [] as $field) {
            if (($field['type'] ?? '') !== 'repeater') {
                continue;
            }

            add_filter("acf/load_value/key={$field['key']}", static function ($value, $postId, $field) use ($class) {
                return $class::presetRows($value, $postId, $field);
            }, 10, 3);
        }
    }

    /**
     * What the form loads for a repeater: what is stored, or the presets keyed by sub-field key
     * when nothing is. A repeater an editor emptied and saved stores 0, and stays empty.
     *
     * @param  array<string, mixed>  $field
     */
    public static function presetRows(mixed $value, int|string|null $postId, array $field): mixed
    {
        if (! empty($value)) {
            return $value;
        }

        if ($postId && function_exists('acf_get_metadata') && acf_get_metadata($postId, $field['name']) !== null) {
            return $value;
        }

        $rows = static::repeaterDefaults()[$field['name']] ?? null;

        return is_array($rows) && $rows !== [] ? self::keyedRows($rows, $field['sub_fields'] ?? []) : $value;
    }

    /**
     * Rows keyed by sub-field key, as ACF's form expects them, with nested repeaters keyed the
     * same way.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  list<array<string, mixed>>  $subFields
     * @return list<array<string, mixed>>
     */
    private static function keyedRows(array $rows, array $subFields): array
    {
        return array_map(static function (array $row) use ($subFields): array {
            $keyed = [];

            foreach ($subFields as $sub) {
                if (! array_key_exists($sub['name'], $row)) {
                    continue;
                }

                $value = $row[$sub['name']];

                $keyed[$sub['key']] = ($sub['type'] ?? '') === 'repeater' && is_array($value)
                    ? self::keyedRows($value, $sub['sub_fields'] ?? [])
                    : $value;
            }

            return $keyed;
        }, array_values($rows));
    }
}

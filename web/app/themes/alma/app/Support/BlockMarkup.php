<?php

namespace App\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Log1x\AcfComposer\Block;
use ReflectionClass;

/**
 * Write ACF block markup from PHP, for patterns.
 *
 *     <?= BlockMarkup::block(\App\Blocks\Hero::class, ['headline' => 'Hello']) ?>
 *
 * ACF stores a block's values inside its comment, and silently ignores a value that has no
 * `_name => field_key` reference beside it. A repeater is stored flattened (`items` = row count,
 * `items_0_title`, ...), each value again with its key. Hand-writing that is how a page ships the
 * block's presets while looking wired up, so patterns go through here instead.
 *
 * Field keys are read from the block's own fields() definition, never guessed, and an unknown
 * field name throws: a typo in a pattern fails loudly (ThemePatterns logs it and skips the file)
 * instead of rendering defaults.
 */
class BlockMarkup
{
    /**
     * Field maps per block class: name => [key, type, sub fields].
     *
     * @var array<class-string, array<string, array{key: string, type: string, sub: array<string, mixed>}>>
     */
    private static array $fields = [];

    /**
     * Block slugs, slug => class.
     *
     * @var array<string, class-string>|null
     */
    private static ?array $slugs = null;

    /**
     * The block comment for an ACF block, by class or slug. Arrays of rows fill repeaters.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $attributes  Extra block attributes (align, anchor, className).
     */
    public static function block(string $block, array $data = [], array $attributes = []): string
    {
        $class = self::resolve($block);
        $slug = self::slug($class);

        $payload = array_merge([
            'name' => 'acf/'.$slug,
            'data' => self::encode($class, $data),
            'mode' => 'preview',
        ], self::defaultAlign($class), $attributes);

        return '<!-- wp:acf/'.$slug.' '.self::serialize($payload).' /-->';
    }

    /**
     * Block data with ACF's field-key references, repeaters flattened.
     *
     * @param  class-string  $class
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function encode(string $class, array $data): array
    {
        return self::encodeFields(self::fields($class), $data, '', $class);
    }

    /**
     * The block class for a class name or a slug.
     *
     * @return class-string<Block>
     */
    public static function resolve(string $block): string
    {
        if (class_exists($block) && is_subclass_of($block, Block::class)) {
            return $block;
        }

        $class = self::slugs()[$block] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("No block with the slug or class [{$block}].");
        }

        return $class;
    }

    /**
     * Every block class in app/Blocks, keyed by slug.
     *
     * @return array<string, class-string>
     */
    public static function slugs(): array
    {
        if (self::$slugs !== null) {
            return self::$slugs;
        }

        self::$slugs = [];

        foreach (glob(dirname(__DIR__).'/Blocks/*.php') ?: [] as $file) {
            $class = 'App\\Blocks\\'.basename($file, '.php');

            if (class_exists($class) && is_subclass_of($class, Block::class) && ! (new ReflectionClass($class))->isAbstract()) {
                self::$slugs[self::slug($class)] = $class;
            }
        }

        return self::$slugs;
    }

    /**
     * @param  class-string  $class
     */
    public static function slug(string $class): string
    {
        $defaults = (new ReflectionClass($class))->getDefaultProperties();

        return $defaults['slug'] ?: Str::slug(Str::kebab((string) $defaults['name']));
    }

    /**
     * The block's default alignment as an attribute, so the markup says what the block renders
     * (and the content flow CSS sees alignfull) without every pattern repeating it.
     *
     * @param  class-string  $class
     * @return array<string, string>
     */
    private static function defaultAlign(string $class): array
    {
        $align = (new ReflectionClass($class))->getDefaultProperties()['align'] ?? '';

        return is_string($align) && $align !== '' ? ['align' => $align] : [];
    }

    /**
     * WordPress' serialize_block_attributes(): JSON with the characters that would end the
     * comment or confuse the block parser escaped.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function serialize(array $attributes): string
    {
        $json = (string) json_encode($attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return strtr($json, [
            '--' => '\\u002d\\u002d',
            '<' => '\\u003c',
            '>' => '\\u003e',
            '&' => '\\u0026',
            '\\"' => '\\u0022',
        ]);
    }

    /**
     * @param  array<string, array{key: string, type: string, sub: array<string, mixed>}>  $fields
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function encodeFields(array $fields, array $data, string $prefix, string $class): array
    {
        $encoded = [];

        foreach ($data as $name => $value) {
            $field = $fields[$name] ?? throw new InvalidArgumentException(
                "Block [{$class}] has no field [{$prefix}{$name}]."
            );

            if ($field['type'] === 'repeater' && is_array($value)) {
                $rows = array_values($value);
                $encoded[$prefix.$name] = count($rows);
                $encoded['_'.$prefix.$name] = $field['key'];

                foreach ($rows as $index => $row) {
                    $encoded += self::encodeFields($field['sub'], (array) $row, "{$prefix}{$name}_{$index}_", $class);
                }

                continue;
            }

            $encoded[$prefix.$name] = $value;
            $encoded['_'.$prefix.$name] = $field['key'];
        }

        return $encoded;
    }

    /**
     * @param  class-string  $class
     * @return array<string, array{key: string, type: string, sub: array<string, mixed>}>
     */
    private static function fields(string $class): array
    {
        if (isset(self::$fields[$class])) {
            return self::$fields[$class];
        }

        // fields() only builds an array, so the block's constructor (which needs ACF Composer's
        // container wiring) is not needed to read it.
        $block = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        return self::$fields[$class] = self::map($block->fields()['fields'] ?? []);
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, array{key: string, type: string, sub: array<string, mixed>}>
     */
    private static function map(array $fields): array
    {
        $map = [];

        foreach ($fields as $field) {
            $map[$field['name']] = [
                'key' => $field['key'],
                'type' => $field['type'],
                'sub' => self::map($field['sub_fields'] ?? []),
            ];
        }

        return $map;
    }
}

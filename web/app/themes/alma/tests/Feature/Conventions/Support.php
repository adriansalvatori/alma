<?php

/*
 * Helpers shared by the convention tests. These tests scan the whole theme, so the helpers are
 * deliberately generic: they discover blocks, views and patterns rather than listing them.
 */

use App\Support\BlockMarkup;
use Log1x\AcfComposer\Block;

if (! function_exists('convention_blocks')) {
    /**
     * Every block class, keyed by slug, instantiated without its constructor (fields() and
     * with() only need the class).
     *
     * @return array<string, Block>
     */
    function convention_blocks(): array
    {
        return array_map(
            fn (string $class) => (new ReflectionClass($class))->newInstanceWithoutConstructor(),
            BlockMarkup::slugs(),
        );
    }

    /**
     * Top-level fields of a block, keyed by name.
     *
     * @return array<string, array<string, mixed>>
     */
    function convention_fields(Block $block): array
    {
        $fields = [];

        foreach ($block->fields()['fields'] ?? [] as $field) {
            $fields[$field['name']] = $field;
        }

        return $fields;
    }

    /**
     * Files under a theme directory matching a suffix, recursively.
     *
     * @return list<string>
     */
    function convention_files(string $directory, string $suffix): array
    {
        $root = theme_path($directory);

        if (! is_dir($root)) {
            return [];
        }

        $files = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
            if (str_ends_with($file->getPathname(), $suffix)) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    function convention_relative(string $path): string
    {
        return ltrim(str_replace(theme_path(), '', $path), '/');
    }

    /**
     * Call a protected method, for reading what a block's with() is built from.
     */
    function convention_call(object $object, string $method, mixed ...$arguments): mixed
    {
        return (fn () => $this->{$method}(...$arguments))->call($object);
    }
}

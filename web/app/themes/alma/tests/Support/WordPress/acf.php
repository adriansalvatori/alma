<?php

/*
 * Fake ACF functions, backed by WordPress::$acf[$postId][$field]. A block's fields live under
 * the 'block' key: seed WordPress::$acf['block'] = ['headline' => '...'] before calling with().
 *
 * Owned by the Content module; other modules use these rather than defining their own.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('get_field')) {
    function get_field(string $selector, mixed $postId = false, bool $formatValue = true, bool $escapeHtml = false): mixed
    {
        return WordPress::$acf[acf_test_key($postId)][$selector] ?? null;
    }
}

if (! function_exists('get_fields')) {
    function get_fields(mixed $postId = false, bool $formatValue = true): array|false
    {
        return WordPress::$acf[acf_test_key($postId)] ?? false;
    }
}

if (! function_exists('update_field')) {
    function update_field(string $selector, mixed $value, mixed $postId = false): bool
    {
        WordPress::$acf[acf_test_key($postId)][$selector] = $value;

        return true;
    }
}

if (! function_exists('acf_test_key')) {
    function acf_test_key(mixed $postId): int|string
    {
        return match (true) {
            $postId === false, $postId === null => 'block',
            $postId === 'options', $postId === 'option' => 'option',
            default => $postId,
        };
    }
}

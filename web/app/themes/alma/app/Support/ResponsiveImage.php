<?php

namespace App\Support;

/**
 * Media-library images as responsive <img> tags.
 *
 * Core already does the hard part (srcset, sizes, width and height from attachment metadata, and
 * WebP sub-sizes when Content's media hook asks for them), so this only normalises what an ACF
 * image field can return and applies the theme's loading defaults.
 */
class ResponsiveImage
{
    /**
     * An attachment as an <img>, or '' when there is none.
     *
     * Lazy and async by default. For the largest image above the fold pass
     * ['loading' => 'eager', 'fetchpriority' => 'high'].
     *
     * @param  int|string|array<string, mixed>|null  $attachment  An ID, or an ACF image array.
     * @param  array<string, string>  $attributes
     */
    public static function html(mixed $attachment, string $size = 'large', array $attributes = []): string
    {
        $id = self::id($attachment);

        if ($id === 0 || ! function_exists('wp_get_attachment_image')) {
            return '';
        }

        return (string) wp_get_attachment_image($id, $size, false, $attributes + [
            'loading' => 'lazy',
            'decoding' => 'async',
        ]);
    }

    /**
     * The attachment ID from whatever an image field returned.
     */
    public static function id(mixed $attachment): int
    {
        return match (true) {
            is_int($attachment) => $attachment,
            is_string($attachment) && ctype_digit($attachment) => (int) $attachment,
            is_array($attachment) => (int) ($attachment['ID'] ?? $attachment['id'] ?? 0),
            default => 0,
        };
    }
}

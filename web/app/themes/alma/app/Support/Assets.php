<?php

namespace App\Support;

/**
 * Images that ship with the theme (not the media library).
 *
 * Source art lives in resources/images/pages/<page>/ and is copied to public/images/<page>/ by
 * vite/theme-images.js at build time, keeping its filename, with a .webp sibling next to every
 * raster. Nothing here touches Node or re-encodes at runtime: shared hosting serves what the
 * build produced.
 *
 * Use Vite::asset() instead for files referenced from CSS/JS; those get hashed names.
 */
class Assets
{
    /**
     * Public URL of a built theme image, e.g. Assets::url('home/hero.jpg').
     */
    public static function url(string $path): string
    {
        return get_theme_file_uri('public/images/'.ltrim($path, '/'));
    }

    /**
     * Absolute file path of a built theme image.
     */
    public static function path(string $path): string
    {
        return get_theme_file_path('public/images/'.ltrim($path, '/'));
    }

    /**
     * Whether the built file exists and is not empty. A zero-byte file is treated as missing:
     * an interrupted upload or build leaves one, and browsers render it as a broken image.
     */
    public static function exists(string $path): bool
    {
        $file = self::path($path);

        return is_file($file) && filesize($file) > 0;
    }

    /**
     * The .webp sibling the build wrote for a raster, when it exists, or the original path.
     *
     * A filename collision (hero.png and hero.jpg in one folder) makes the build write
     * hero.jpg.webp for the JPEG, so that form is checked first.
     */
    public static function webp(string $path): string
    {
        if (! preg_match('/\.(png|jpe?g)$/i', $path)) {
            return $path;
        }

        foreach ([$path.'.webp', preg_replace('/\.(png|jpe?g)$/i', '.webp', $path)] as $candidate) {
            if (self::exists($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }

    /**
     * Width and height of a built image, read once per file version and cached.
     *
     * getimagesize() on every request is a disk read per image per page view, which adds up fast
     * on shared hosting. The cache key includes the modification time, so a rebuilt image is
     * measured again.
     *
     * @return array{width: int, height: int}|null
     */
    public static function dimensions(string $path): ?array
    {
        $file = self::path($path);

        if (! is_file($file)) {
            return null;
        }

        $key = 'alma_img_'.md5($file.'|'.filemtime($file));
        $cached = get_transient($key);

        if (is_array($cached)) {
            return $cached;
        }

        $size = str_ends_with(strtolower($file), '.svg') ? self::svgSize($file) : @getimagesize($file);

        if (! $size || empty($size[0]) || empty($size[1])) {
            return null;
        }

        $dimensions = ['width' => (int) $size[0], 'height' => (int) $size[1]];
        set_transient($key, $dimensions, MONTH_IN_SECONDS);

        return $dimensions;
    }

    /**
     * A complete <img> for a built theme image: the WebP when there is one, the file's own
     * width and height (so the browser reserves its space), lazy and async unless overridden.
     *
     * @param  array<string, string|int|bool>  $attributes
     */
    public static function img(string $path, string $alt = '', array $attributes = []): string
    {
        $source = self::webp($path);
        $attributes = $attributes + (self::dimensions($path) ?? []) + [
            'loading' => 'lazy',
            'decoding' => 'async',
        ];

        $html = '<img src="'.esc_url(self::url($source)).'" alt="'.esc_attr($alt).'"';

        foreach ($attributes as $name => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            $html .= $value === true ? ' '.esc_attr($name) : ' '.esc_attr($name).'="'.esc_attr((string) $value).'"';
        }

        return $html.'>';
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private static function svgSize(string $file): ?array
    {
        $svg = (string) file_get_contents($file, false, null, 0, 2048);

        if (preg_match('/\bwidth="([\d.]+)(?:px)?"[^>]*\bheight="([\d.]+)(?:px)?"/', $svg, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        if (preg_match('/viewBox="[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)"/', $svg, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return null;
    }
}

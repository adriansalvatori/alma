<?php

namespace App\Domains\Seo\Redirects;

/**
 * One normal form for request paths, so "/Old-Page", "/old-page/" and
 * "https://example.com/old-page?x=1" all find the same redirect.
 */
final class RedirectPath
{
    /**
     * The path part of $value with a leading slash and no trailing slash (except "/"). Returns ''
     * when $value is an absolute URL on another host, or has no path.
     */
    public static function normalize(string $value, ?string $homeHost = null): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) || str_starts_with($value, '//')) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));

            if ($homeHost !== null && $host !== strtolower($homeHost)) {
                return '';
            }

            $value = (string) (parse_url($value, PHP_URL_PATH) ?? '/');
        }

        $path = (string) (parse_url('http://x'.(str_starts_with($value, '/') ? '' : '/').$value, PHP_URL_PATH) ?? '');
        $path = '/'.trim((string) preg_replace('#/+#', '/', rawurldecode($path)), '/');

        return $path;
    }

    /**
     * The lookup key: the normalized path, lowercased.
     */
    public static function key(string $value, ?string $homeHost = null): string
    {
        return strtolower(self::normalize($value, $homeHost));
    }
}

<?php

namespace App\Infrastructure\Cache;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A cache wrapper for code paths that must not fail because the cache did: reading a cache that
 * errors behaves like a miss, writing to it is skipped. The error is logged once per request.
 *
 * Use it on the paths a visitor or a payment depends on (a form submission, a webhook, a rate
 * limit that must not lock everyone out). Shared hosting's file cache fails in mundane ways: a
 * full disk, a permissions change after a deploy, a quota. Elsewhere use the Cache facade.
 */
class SoftCache
{
    private bool $reported = false;

    public function __construct(private readonly Repository $cache) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attempt(fn () => $this->cache->get($key, $default), $default);
    }

    public function put(string $key, mixed $value, \DateTimeInterface|\DateInterval|int|null $ttl = null): bool
    {
        return (bool) $this->attempt(fn () => $this->cache->put($key, $value, $ttl), false);
    }

    /**
     * Return the cached value or compute it. When the cache is broken the value is computed on
     * every call, which is slower but correct.
     */
    public function remember(string $key, \DateTimeInterface|\DateInterval|int|null $ttl, Closure $callback): mixed
    {
        $cached = $this->attempt(fn () => $this->cache->get($key), null);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    public function forget(string $key): bool
    {
        return (bool) $this->attempt(fn () => $this->cache->forget($key), false);
    }

    private function attempt(Closure $operation, mixed $fallback): mixed
    {
        try {
            return $operation();
        } catch (Throwable $e) {
            if (! $this->reported) {
                $this->reported = true;
                Log::warning('Cache unavailable, continuing without it: '.$e->getMessage());
            }

            return $fallback;
        }
    }
}

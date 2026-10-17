<?php

namespace App\Domains\Payment\Support;

/**
 * HMAC-SHA256 webhook signatures in the "t=<timestamp>,v1=<hex>" form Stripe popularised.
 *
 * The timestamp is signed with the body, so a captured request cannot be replayed after the
 * tolerance window. An empty secret never verifies: a gateway without its signing secret must
 * refuse webhooks, not accept them all.
 */
final class WebhookSignature
{
    public const TOLERANCE = 300;

    public static function sign(string $payload, string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }

    public static function verify(
        string $payload,
        ?string $header,
        string $secret,
        int $tolerance = self::TOLERANCE,
        ?int $now = null,
    ): bool {
        if ($secret === '' || $header === null || $header === '') {
            return false;
        }

        $parts = [];

        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, '');
            $parts[$key][] = $value;
        }

        $timestamp = (int) ($parts['t'][0] ?? 0);

        if ($timestamp <= 0 || abs(($now ?? time()) - $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        // Several v1 values may be present while a secret is being rotated.
        foreach ($parts['v1'] ?? [] as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }
}

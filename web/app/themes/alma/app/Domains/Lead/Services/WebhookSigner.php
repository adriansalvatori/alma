<?php

namespace App\Domains\Lead\Services;

/**
 * Signs outgoing lead webhooks in the same "t=<unix time>,v1=<hex HMAC-SHA256>" shape Stripe uses,
 * over "<t>.<raw body>". Receivers recompute the HMAC with the shared secret and reject a mismatch
 * or a stale timestamp; docs/lead.md has a verification snippet.
 */
class WebhookSigner
{
    public const HEADER = 'X-Alma-Signature';

    public function sign(string $body, string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$body, $secret);
    }

    public function verify(string $body, string $header, string $secret, int $tolerance = 300): bool
    {
        if ($secret === '' || ! preg_match('/^t=(\d+),v1=([a-f0-9]{64})$/', $header, $parts)) {
            return false;
        }

        if (abs(time() - (int) $parts[1]) > $tolerance) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $parts[1].'.'.$body, $secret), $parts[2]);
    }
}

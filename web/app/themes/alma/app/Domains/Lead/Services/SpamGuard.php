<?php

namespace App\Domains\Lead\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Cheap, invisible spam checks. No captcha, no third party.
 *
 * - Honeypot: a field humans never see. Anything in it is a bot.
 * - Fill time: the form carries its render time, encrypted with APP_KEY. A submission faster than
 *   a human can type is a bot. There is deliberately no maximum age: a page cache serves old
 *   tokens, and an old token only means the visitor took their time.
 * - Rate limit: a few submissions per IP per ten minutes, counted on every attempt.
 * - Blocklist: emails or whole domains from Leads → Settings or LEAD_BLOCKLIST.
 */
class SpamGuard
{
    public const HONEYPOT = 'honeypot';

    public const TOKEN = 'token';

    public const TOO_FAST = 'too_fast';

    public const RATE_LIMITED = 'rate_limited';

    public const BLOCKLISTED = 'blocklisted';

    private const WINDOW_SECONDS = 600;

    public function __construct(private LeadSettings $settings) {}

    /**
     * A token recording when the form was rendered.
     */
    public function issueToken(?int $time = null): string
    {
        return Crypt::encryptString((string) ($time ?? time()));
    }

    /**
     * Why this submission is spam, or null when it looks human. Every call counts against the
     * IP's rate limit.
     */
    public function inspect(string $honeypot, string $token, string $email, ?string $ip): ?string
    {
        if (trim($honeypot) !== '') {
            return self::HONEYPOT;
        }

        $renderedAt = $this->renderedAt($token);

        if ($renderedAt === null) {
            return self::TOKEN;
        }

        if (time() - $renderedAt < (int) config('leads.spam.min_seconds', 3)) {
            return self::TOO_FAST;
        }

        if ($ip !== null && $this->rateLimited($ip)) {
            return self::RATE_LIMITED;
        }

        if ($this->blocklisted($email)) {
            return self::BLOCKLISTED;
        }

        return null;
    }

    public function blocklisted(string $email): bool
    {
        $email = strtolower(trim($email));
        $domain = substr(strrchr($email, '@') ?: '', 1);

        foreach ($this->settings->blocklist() as $entry) {
            if ($entry === $email || ($domain !== '' && ($entry === $domain || str_ends_with($domain, '.'.$entry)))) {
                return true;
            }
        }

        return false;
    }

    private function renderedAt(string $token): ?int
    {
        if ($token === '') {
            return null;
        }

        try {
            $value = Crypt::decryptString($token);
        } catch (DecryptException) {
            return null;
        }

        return ctype_digit($value) ? (int) $value : null;
    }

    private function rateLimited(string $ip): bool
    {
        $key = 'alma-lead:'.hash('sha256', $ip);

        // A cache outage must not block real leads: fail open.
        try {
            if (RateLimiter::tooManyAttempts($key, (int) config('leads.spam.max_attempts', 5))) {
                return true;
            }

            RateLimiter::hit($key, self::WINDOW_SECONDS);
        } catch (\Throwable) {
            return false;
        }

        return false;
    }
}

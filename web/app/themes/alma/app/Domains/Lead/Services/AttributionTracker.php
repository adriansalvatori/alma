<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Data\Attribution;
use App\Domains\Lead\Data\Touch;
use Illuminate\Support\Carbon;

/**
 * First-party attribution in one cookie, set server-side, no JavaScript.
 *
 * A visit is a touch when its URL carries utm_* or an ad click id, or when it arrives from another
 * site. Direct and internal visits change nothing, so navigating around the site never overwrites
 * the campaign that brought the visitor in.
 *
 * The cookie only records where a visit came from, never who the visitor is, and is read only when
 * that visitor submits a form.
 */
class AttributionTracker
{
    /**
     * The touch this request represents, or null for a direct or internal visit.
     *
     * @param  array<string, mixed>  $query
     */
    public function touchFrom(array $query, ?string $referrer, string $url): ?Touch
    {
        $values = [];

        foreach (Touch::PARAMETERS as $parameter => $key) {
            if (isset($query[$parameter]) && is_scalar($query[$parameter]) && trim((string) $query[$parameter]) !== '') {
                $values[$key] = $query[$parameter];
            }
        }

        $external = $this->externalReferrer($referrer);

        if ($values === [] && $external === null) {
            return null;
        }

        // A referral without campaign tags still says where the visitor came from.
        if ($values === [] && $external !== null) {
            $values = ['source' => parse_url($external, PHP_URL_HOST), 'medium' => 'referral'];
        }

        return Touch::fromArray($values + [
            'referrer' => $external,
            'landing_page' => $this->withoutQuery($url),
            'captured_at' => Carbon::now()->toIso8601String(),
        ]);
    }

    /**
     * Record this request's touch, if it is one. Returns the attribution now in effect.
     *
     * @param  array<string, mixed>  $query
     */
    public function track(array $query, ?string $referrer, string $url): Attribution
    {
        $current = $this->current();
        $touch = $this->touchFrom($query, $referrer, $url);

        if ($touch === null) {
            return $current;
        }

        $attribution = $current->with($touch);
        $this->store($attribution);

        return $attribution;
    }

    /**
     * The attribution in the visitor's cookie. A missing or tampered cookie reads as empty.
     */
    public function current(): Attribution
    {
        $raw = $_COOKIE[$this->cookieName()] ?? null;

        if (! is_string($raw) || $raw === '' || strlen($raw) > 4096) {
            return new Attribution;
        }

        $data = json_decode(wp_unslash($raw), true);

        return is_array($data) ? Attribution::fromArray($data) : new Attribution;
    }

    public function store(Attribution $attribution): void
    {
        $value = (string) wp_json_encode($attribution->toArray());

        // Visible to the rest of this request too, so a form submitted on the landing page itself
        // carries the touch that brought the visitor there.
        $_COOKIE[$this->cookieName()] = $value;

        if (headers_sent()) {
            return;
        }

        setcookie($this->cookieName(), $value, [
            'expires' => time() + DAY_IN_SECONDS * (int) config('leads.attribution.days', 90),
            'path' => defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/',
            'domain' => defined('COOKIE_DOMAIN') && COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function cookieName(): string
    {
        return (string) config('leads.attribution.cookie', 'alma_attribution');
    }

    private function externalReferrer(?string $referrer): ?string
    {
        $host = is_string($referrer) ? parse_url($referrer, PHP_URL_HOST) : null;

        if (! is_string($host) || $host === '') {
            return null;
        }

        $home = (string) parse_url(home_url(), PHP_URL_HOST);

        return strcasecmp($this->bareHost($host), $this->bareHost($home)) === 0 ? null : Touch::clean($referrer, 500);
    }

    private function bareHost(string $host): string
    {
        return preg_replace('/^www\./i', '', $host) ?? $host;
    }

    private function withoutQuery(string $url): string
    {
        return strtok($url, '?') ?: $url;
    }
}

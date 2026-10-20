<?php

namespace App\Domains\Lead\Services;

/**
 * The avatar shown beside a lead: their company's logo, or their initials.
 *
 * The logo is the favicon of the email's domain, fetched by the admin's browser from Google's
 * favicon service. Two things make that safe to do from an admin list:
 *
 * 1. Only the **domain** is ever sent. The lead's address never leaves the server, and neither
 *    does a hash of it, which is the reason this is not Gravatar. A CRM's list view should not
 *    hand a third party the means to recognise every person in the pipeline.
 * 2. An unknown domain answers **404**, not a placeholder with a 200. So the browser's own
 *    `onerror` is a reliable signal and the initials underneath simply show through. Nothing has
 *    to probe the service from PHP, and a row never sits on a generic grey globe.
 *
 * Consumer mailboxes are excluded deliberately: rendering the Gmail logo on half the list says
 * nothing about who anyone is. Those fall back to initials, tinted per person so a long list is
 * still scannable.
 *
 * Ported from Remote Leverage's Leads screen.
 */
class LeadAvatar
{
    /** Where the browser asks for a domain's icon. Unknown domains answer 404 here. */
    private const FAVICON_ENDPOINT = 'https://www.google.com/s2/favicons';

    /** Rendered at 36px; 64 keeps it crisp on a 2x display. */
    private const FAVICON_SIZE = 64;

    /**
     * Mailbox providers that say nothing about where someone works.
     *
     * Exact hosts only: the prefix families below cover the country variants, which is how
     * `outlook.es` and the rest of `yahoo.*` get caught without listing all 40-odd of them.
     */
    private const CONSUMER_DOMAINS = [
        'gmail.com', 'googlemail.com', 'aol.com', 'icloud.com', 'me.com', 'mac.com',
        'protonmail.com', 'proton.me', 'pm.me', 'gmx.com', 'gmx.net', 'gmx.de', 'web.de', 'mail.com',
        'zoho.com', 'yandex.com', 'yandex.ru', 'inbox.com', 'fastmail.com', 'example.com',
        // ISP mailboxes, still in heavy use and never a company.
        'comcast.net', 'sbcglobal.net', 'bellsouth.net', 'verizon.net', 'att.net',
        'cox.net', 'charter.net', 'earthlink.net', 'roadrunner.com', 'rr.com',
        'optonline.net', 'juno.com', 'netzero.net', 'frontier.com', 'windstream.net',
    ];

    /** Provider families whose country variants all mean the same thing. */
    private const CONSUMER_PREFIXES = [
        'yahoo.', 'hotmail.', 'outlook.', 'live.', 'msn.', 'ymail.', 'rocketmail.',
    ];

    /**
     * Initials background/foreground pairs, walked by a hash of the address.
     *
     * Every pair clears 4.5:1 against its own background, because these carry the only text on
     * the row that identifies a person when no logo resolves.
     */
    private const TINTS = [
        ['#e0e7ff', '#3730a3'], // indigo
        ['#ccfbf1', '#115e59'], // teal
        ['#fef3c7', '#92400e'], // amber
        ['#fce7f3', '#9d174d'], // pink
        ['#dcfce7', '#166534'], // green
        ['#e0f2fe', '#075985'], // sky
        ['#ede9fe', '#5b21b6'], // violet
        ['#ffedd5', '#9a3412'], // orange
    ];

    /** The domain part of an address, lowercased, or null if there is not one. */
    public static function domain(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if ($email === '' || ! str_contains($email, '@')) {
            return null;
        }

        $at = (int) strrpos($email, '@');
        $domain = trim(substr($email, $at + 1));

        // An empty local part is not an address, a domain with no dot is not a domain, and
        // neither is worth asking a logo service about.
        if (substr($email, 0, $at) === '' || $domain === '' || ! str_contains($domain, '.')) {
            return null;
        }

        return $domain;
    }

    /** Is this a mailbox provider rather than a company? */
    public static function isConsumerDomain(?string $domain): bool
    {
        $domain = strtolower(trim((string) $domain));

        if ($domain === '' || in_array($domain, self::CONSUMER_DOMAINS, true)) {
            return true;
        }

        foreach (self::CONSUMER_PREFIXES as $prefix) {
            if (str_starts_with($domain, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Where to fetch this lead's company logo, or null when there is no company to show.
     *
     * Null means "render initials and request nothing": the caller must not emit an <img> at all,
     * so a consumer mailbox costs no outbound request.
     */
    public static function logoUrl(?string $email): ?string
    {
        $domain = self::domain($email);

        if ($domain === null || self::isConsumerDomain($domain)) {
            return null;
        }

        return self::FAVICON_ENDPOINT.'?'.http_build_query([
            'domain' => $domain,
            'sz' => self::FAVICON_SIZE,
        ]);
    }

    /** One or two letters standing in for a lead with no logo. */
    public static function initials(?string $name, ?string $email): string
    {
        $name = trim((string) $name);

        if ($name !== '') {
            $parts = preg_split('/\s+/', $name) ?: [];

            if (count($parts) >= 2) {
                return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr((string) end($parts), 0, 1));
            }

            return mb_strtoupper(mb_substr($name, 0, 2));
        }

        $email = trim((string) $email);

        return $email !== '' ? mb_strtoupper(mb_substr($email, 0, 2)) : '?';
    }

    /**
     * A stable background/foreground pair for one lead's initials.
     *
     * Keyed on the address rather than the name so the same person keeps their colour when a
     * later submission fills in who they are.
     *
     * @return array{0: string, 1: string}
     */
    public static function tint(?string $email, ?string $name = null): array
    {
        $key = strtolower(trim((string) $email)) ?: strtolower(trim((string) $name));

        if ($key === '') {
            return self::TINTS[0];
        }

        return self::TINTS[hexdec(substr(md5($key), 0, 8)) % count(self::TINTS)];
    }
}

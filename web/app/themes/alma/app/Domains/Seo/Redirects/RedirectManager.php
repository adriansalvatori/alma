<?php

namespace App\Domains\Seo\Redirects;

use Illuminate\Support\Facades\DB;

/**
 * Creates and deletes editor-managed redirects, refusing the ones that would break the site.
 *
 * Every rule here exists because the mistake is easy to make in a form and expensive to notice:
 * a redirect from /wp-admin locks everyone out, one from "/" hides the homepage, a loop takes a
 * page down, and an http:// target downgrades visitors off-site.
 */
class RedirectManager
{
    public const STATUSES = [301, 302, 307, 308];

    /**
     * First path segments a redirect may never start from.
     */
    public const RESERVED = ['wp', 'wp-admin', 'wp-json', 'wp-login.php', 'wp-cron.php', 'xmlrpc.php', 'wp-content', 'app', 'api', 'livewire', '.well-known', 'robots.txt', 'sitemap.xml'];

    private const MAX_HOPS = 10;

    public function __construct(private readonly RedirectStore $store) {}

    /**
     * @throws RedirectRejected
     */
    public function create(string $from, string $to, int $status = 301, ?string $note = null, int $userId = 0): Redirect
    {
        $home = RedirectStore::homeHost();
        $fromPath = $this->validFrom($from, $home);
        $toUrl = $this->validTo($to, $home);

        if (! in_array($status, self::STATUSES, true)) {
            throw new RedirectRejected('Use a 301 (permanent) or 302 (temporary) redirect.');
        }

        $this->assertNoLoop($fromPath, $toUrl);

        $redirect = Redirect::query()->create([
            'from_path' => $fromPath,
            'from_key' => strtolower($fromPath),
            'to_url' => $toUrl,
            'status_code' => $status,
            'note' => $note !== null && trim($note) !== '' ? mb_substr(trim($note), 0, 255) : null,
            'created_by' => $userId > 0 ? $userId : null,
        ]);

        $this->store->rebuild();

        return $redirect;
    }

    public function delete(int $id): bool
    {
        $deleted = (bool) Redirect::query()->whereKey($id)->delete();

        if ($deleted) {
            $this->store->rebuild();
        }

        return $deleted;
    }

    /**
     * Count a hit. Best effort: a failed update must never cost the visitor their redirect.
     */
    public function recordHit(int $id): void
    {
        try {
            Redirect::query()->whereKey($id)->update([
                'hits' => DB::raw('hits + 1'),
                'last_hit_at' => now(),
            ]);
        } catch (\Throwable) {
            //
        }
    }

    private function validFrom(string $from, string $home): string
    {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', trim($from)) && strtolower((string) parse_url(trim($from), PHP_URL_HOST)) !== $home) {
            throw new RedirectRejected('The source must be a path on this site.');
        }

        $path = RedirectPath::normalize($from, $home);

        if ($path === '' || $path === '/') {
            throw new RedirectRejected('The homepage cannot be redirected here. Change the front page in Settings → Reading instead.');
        }

        if (mb_strlen($path) > 255) {
            throw new RedirectRejected('The source path is longer than 255 characters.');
        }

        $first = strtolower(explode('/', ltrim($path, '/'))[0]);

        if (in_array($first, self::RESERVED, true)) {
            throw new RedirectRejected("Paths under /{$first} belong to WordPress or the application and cannot be redirected.");
        }

        if (array_key_exists(strtolower($path), $this->store->codeMap())) {
            throw new RedirectRejected("{$path} is already redirected in code (config/redirects.php).");
        }

        if (Redirect::query()->where('from_key', strtolower($path))->exists()) {
            throw new RedirectRejected("{$path} is already redirected.");
        }

        return $path;
    }

    /**
     * Same-site targets are stored as paths, so the database still works after it is copied to
     * another environment with a different domain.
     */
    private function validTo(string $to, string $home): string
    {
        $to = trim($to);

        if ($to === '') {
            throw new RedirectRejected('Enter where the redirect should go.');
        }

        if (mb_strlen($to) > 2048) {
            throw new RedirectRejected('The target URL is longer than 2048 characters.');
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $to) || str_starts_with($to, '//')) {
            $scheme = strtolower((string) parse_url($to, PHP_URL_SCHEME));
            $host = strtolower((string) parse_url($to, PHP_URL_HOST));

            if ($host === '') {
                throw new RedirectRejected('The target URL is not valid.');
            }

            if ($host === $home) {
                return $this->pathWithQuery($to);
            }

            if ($scheme !== 'https') {
                throw new RedirectRejected('Off-site targets must use https://.');
            }

            return $to;
        }

        if (! str_starts_with($to, '/')) {
            throw new RedirectRejected('Start the target with / for a page on this site, or https:// for another site.');
        }

        return $this->pathWithQuery($to);
    }

    private function pathWithQuery(string $url): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: '/');
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        return $path.($query ? "?{$query}" : '').($fragment ? "#{$fragment}" : '');
    }

    /**
     * Follow the chain from the target through existing redirects; refuse if it comes back.
     */
    private function assertNoLoop(string $from, string $to): void
    {
        $fromKey = strtolower($from);
        $home = RedirectStore::homeHost();
        $map = $this->store->map();
        $next = $to;

        for ($hop = 0; $hop < self::MAX_HOPS; $hop++) {
            $key = RedirectPath::key($next, $home);

            if ($key === '') {
                return; // Off-site: the chain leaves this site.
            }

            if ($key === $fromKey) {
                throw new RedirectRejected($hop === 0
                    ? 'A redirect cannot point at itself.'
                    : "This would create a redirect loop back to {$from}.");
            }

            if (! isset($map[$key])) {
                return;
            }

            $next = $map[$key]['to'];
        }

        throw new RedirectRejected('The target starts a chain of more than '.self::MAX_HOPS.' redirects.');
    }
}

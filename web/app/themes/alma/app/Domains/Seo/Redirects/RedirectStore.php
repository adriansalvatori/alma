<?php

namespace App\Domains\Seo\Redirects;

use Throwable;

/**
 * The compiled redirect lookup: code map plus database rows, keyed by normalized lowercase path.
 *
 * The database half is kept in one autoloaded option, so a page view costs no extra query (all
 * autoloaded options arrive in the single query WordPress makes anyway). It is rebuilt whenever a
 * redirect is saved or deleted, and lazily if missing. Every web server reads the same option,
 * so there is no per-server cache to go stale.
 */
class RedirectStore
{
    public const OPTION = 'alma_redirects_compiled';

    /** @var array<string, array{to: string, status: int, source: string, id: int|null}>|null */
    private ?array $map = null;

    /**
     * @return array{to: string, status: int, source: string, id: int|null}|null
     */
    public function find(string $path): ?array
    {
        $key = RedirectPath::key($path, self::homeHost());

        return $key === '' ? null : ($this->map()[$key] ?? null);
    }

    /**
     * Code redirects win over database ones with the same path: a deploy is the authority.
     *
     * @return array<string, array{to: string, status: int, source: string, id: int|null}>
     */
    public function map(): array
    {
        return $this->map ??= $this->codeMap() + $this->databaseMap();
    }

    /**
     * @return array<string, array{to: string, status: int, source: string, id: int|null}>
     */
    public function codeMap(): array
    {
        $map = [];

        foreach ((array) config('redirects', []) as $from => $target) {
            $to = is_array($target) ? (string) ($target['to'] ?? '') : (string) $target;
            $status = is_array($target) ? (int) ($target['status'] ?? 301) : 301;
            $key = RedirectPath::key((string) $from, self::homeHost());

            if ($key !== '' && $key !== '/' && $to !== '') {
                $map[$key] = ['to' => $to, 'status' => $status, 'source' => 'code', 'id' => null];
            }
        }

        return $map;
    }

    /**
     * @return array<string, array{to: string, status: int, source: string, id: int|null}>
     */
    public function databaseMap(): array
    {
        $compiled = get_option(self::OPTION);

        if (is_array($compiled) && isset($compiled['map']) && is_array($compiled['map'])) {
            return $compiled['map'];
        }

        return $this->rebuild();
    }

    /**
     * Recompile the database half from the table. Called after every change.
     *
     * @return array<string, array{to: string, status: int, source: string, id: int|null}>
     */
    public function rebuild(): array
    {
        $map = [];

        try {
            foreach (Redirect::query()->orderBy('id')->get() as $redirect) {
                $map[$redirect->from_key] = [
                    'to' => $redirect->to_url,
                    'status' => $redirect->status_code,
                    'source' => 'database',
                    'id' => $redirect->id,
                ];
            }
        } catch (Throwable) {
            // The table does not exist before the first deploy. No redirects, no error.
            return [];
        }

        update_option(self::OPTION, ['map' => $map, 'built_at' => time()], true);
        $this->map = null;

        return $map;
    }

    public function forget(): void
    {
        $this->map = null;
    }

    /**
     * Hosts of off-site targets, for wp_safe_redirect's allowlist.
     *
     * @return list<string>
     */
    public function externalHosts(): array
    {
        $home = self::homeHost();

        return collect($this->map())
            ->map(fn (array $redirect) => strtolower((string) parse_url($redirect['to'], PHP_URL_HOST)))
            ->filter(fn (string $host) => $host !== '' && $host !== $home)
            ->unique()
            ->values()
            ->all();
    }

    public static function homeHost(): string
    {
        return strtolower((string) parse_url(home_url(), PHP_URL_HOST));
    }
}

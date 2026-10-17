<?php

namespace App\Domains\Seo\Redirects;

/**
 * Serve redirects on page requests.
 *
 * WordPress page requests never pass through Laravel middleware, so this is a template_redirect
 * hook. Priority 0 runs it before core's canonical redirect and anything a plugin adds, so a
 * configured redirect always wins.
 */
class ApplyRedirects
{
    public function __construct(
        private readonly RedirectStore $store,
        private readonly RedirectManager $manager,
    ) {}

    public function register(): void
    {
        add_action('template_redirect', [$this, 'handle'], 0);
        add_filter('allowed_redirect_hosts', [$this, 'allowHosts']);
    }

    public function handle(): void
    {
        $match = $this->match((string) ($_SERVER['REQUEST_URI'] ?? ''));

        if ($match === null) {
            return;
        }

        if ($match['id'] !== null) {
            $this->manager->recordHit($match['id']);
        }

        // A redirect that carries a visitor's query string (campaign parameters) must not be
        // stored by a shared cache and replayed to the next visitor.
        if (str_contains($match['location'], '?') && ! headers_sent()) {
            header('Cache-Control: private, no-store');
        }

        if (wp_safe_redirect($match['location'], $match['status'], 'Alma')) {
            $this->terminate();
        }
    }

    /**
     * Where a request should go, or null when no redirect applies.
     *
     * @return array{location: string, status: int, id: int|null}|null
     */
    public function match(string $requestUri): ?array
    {
        $redirect = $this->store->find((string) (parse_url($requestUri, PHP_URL_PATH) ?? ''));

        if ($redirect === null) {
            return null;
        }

        $location = str_starts_with($redirect['to'], '/') ? home_url($redirect['to']) : $redirect['to'];
        $query = (string) (parse_url($requestUri, PHP_URL_QUERY) ?? '');

        if ($query !== '' && ! str_contains($location, '?')) {
            $location .= (str_contains($location, '#') ? '' : '?'.$query);
        }

        // Defensive: never answer a request with a redirect to itself.
        if (RedirectPath::key($location, RedirectStore::homeHost()) === RedirectPath::key($requestUri, RedirectStore::homeHost())
            && parse_url($location, PHP_URL_HOST) === parse_url(home_url(), PHP_URL_HOST)) {
            return null;
        }

        return ['location' => $location, 'status' => $redirect['status'], 'id' => $redirect['id']];
    }

    /**
     * @param  list<string>  $hosts
     * @return list<string>
     */
    public function allowHosts(array $hosts): array
    {
        return array_values(array_unique([...$hosts, ...$this->store->externalHosts()]));
    }

    protected function terminate(): never
    {
        exit;
    }
}

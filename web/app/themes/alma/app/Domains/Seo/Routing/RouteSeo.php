<?php

namespace App\Domains\Seo\Routing;

use App\Domains\Seo\Robots\PageRobots;
use Illuminate\Routing\Route;
use Throwable;

/**
 * SEO metadata for pages Acorn routes (login, dashboard, any routes/web.php page).
 *
 * WordPress resolves no post for these, so The SEO Framework would describe them as the
 * homepage: the homepage's title, description, canonical and Open Graph tags on /login. When an
 * Acorn route is serving the request, TSF is told the query has no SEO support (it then prints
 * nothing) and this class prints the title, description, canonical and robots itself.
 *
 * Routes behind the auth or guest middleware are noindexed automatically. A controller can set
 * everything explicitly:
 *
 *   RouteSeo::set(title: 'Pricing', description: '...', noindex: false);
 */
class RouteSeo
{
    /** @var array{title?: string, description?: string, canonical?: string, noindex?: bool} */
    private static array $meta = [];

    public static function set(?string $title = null, ?string $description = null, ?string $canonical = null, ?bool $noindex = null): void
    {
        self::$meta = array_filter(
            ['title' => $title, 'description' => $description, 'canonical' => $canonical, 'noindex' => $noindex],
            fn ($value) => $value !== null,
        ) + self::$meta;
    }

    public static function reset(): void
    {
        self::$meta = [];
    }

    public function register(): void
    {
        add_filter('the_seo_framework_query_supports_seo', [$this, 'theSeoFrameworkSupport'], 20);
        add_filter('pre_get_document_title', [$this, 'title'], 99);
        add_filter('the_seo_framework_pre_get_document_title', [$this, 'title'], 99);
        add_action('wp_head', [$this, 'head'], 1);
        // Before RobotsMeta (priority 20) turns PageRobots into the robots tag.
        add_filter('wp_robots', function (array $robots) {
            $this->applyRobots();

            return $robots;
        }, 1);
    }

    public function theSeoFrameworkSupport(mixed $supported): bool
    {
        return $this->route() === null && (bool) $supported;
    }

    public function title(mixed $title): mixed
    {
        if ($this->route() === null || empty(self::$meta['title'])) {
            return $title;
        }

        $separator = (string) apply_filters('document_title_separator', '|');

        return self::$meta['title'].' '.$separator.' '.get_bloginfo('name');
    }

    public function head(): void
    {
        if ($this->route() === null) {
            return;
        }

        $this->applyRobots();

        if (! empty(self::$meta['description'])) {
            echo '<meta name="description" content="'.esc_attr(self::$meta['description']).'">'."\n";
        }

        echo '<link rel="canonical" href="'.esc_url($this->canonical()).'">'."\n";
    }

    /**
     * Noindex the route through PageRobots, so every robots output agrees.
     */
    public function applyRobots(): void
    {
        $route = $this->route();

        if ($route !== null && (self::$meta['noindex'] ?? $this->isPrivate($route))) {
            PageRobots::forceNoindex();
        }
    }

    public function canonical(): string
    {
        if (! empty(self::$meta['canonical'])) {
            return self::$meta['canonical'];
        }

        // The requested path, not the route's URI pattern (which may hold {placeholders}).
        $path = trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH), '/');

        return home_url($path === '' ? '/' : "/{$path}");
    }

    protected function isPrivate(Route $route): bool
    {
        return array_intersect(['auth', 'guest'], $route->gatherMiddleware()) !== [];
    }

    /**
     * The Acorn route serving this request, or null on pages WordPress renders.
     */
    protected function route(): ?Route
    {
        try {
            $route = app('router')->current();
        } catch (Throwable) {
            return null;
        }

        return $route instanceof Route && $route->getName() !== 'wordpress' ? $route : null;
    }
}

<?php

namespace App\Domains\Seo\Robots;

/**
 * Applies PageRobots to the robots meta tag, through core's wp_robots and, when it is active,
 * The SEO Framework (which replaces core's robots output with its own).
 */
class RobotsMeta
{
    public function __construct(private readonly PageRobots $robots) {}

    public function register(): void
    {
        add_filter('wp_robots', [$this, 'core'], 20);
        add_filter('the_seo_framework_robots_meta_array', [$this, 'theSeoFramework'], 20, 2);
    }

    /**
     * @param  array<string, bool|string>  $robots
     * @return array<string, bool|string>
     */
    public function core(array $robots): array
    {
        $directive = $this->robots->current();

        if ($directive === null) {
            return $robots;
        }

        unset($robots['index'], $robots['max-snippet'], $robots['max-image-preview'], $robots['max-video-preview']);
        $robots['noindex'] = true;

        if ($directive === PageRobots::NOINDEX) {
            unset($robots['follow']);
            $robots['nofollow'] = true;
        } else {
            unset($robots['nofollow']);
            $robots['follow'] = true;
        }

        return $robots;
    }

    /**
     * TSF passes ['id' => ...] when it asks about a specific post (sitemaps, the SEO bar) and
     * null for the current query. Terms and archives are left to TSF.
     *
     * @param  array<string, string>  $meta
     * @param  array<string, mixed>|null  $args
     * @return array<string, string>
     */
    public function theSeoFramework(array $meta, ?array $args = null): array
    {
        $directive = match (true) {
            $args === null => $this->robots->current(),
            ! empty($args['tax']) || ! empty($args['pta']) || ! empty($args['uid']) => null,
            default => $this->robots->directiveFor((int) ($args['id'] ?? 0)),
        };

        if ($directive === null) {
            return $meta;
        }

        $meta['noindex'] = 'noindex';

        if ($directive === PageRobots::NOINDEX) {
            $meta['nofollow'] = 'nofollow';
        }

        return $meta;
    }
}

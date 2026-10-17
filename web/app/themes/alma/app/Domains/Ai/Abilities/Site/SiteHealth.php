<?php

namespace App\Domains\Ai\Abilities\Site;

use App\Domains\Ai\Abilities\Ability;
use App\Infrastructure\Observability\Health\CronHealth;
use App\Infrastructure\Observability\Health\HealthChecker;
use Closure;
use Throwable;
use WP_Error;

/**
 * A snapshot of the site an agent can read before it changes anything, and the first thing to
 * ask for when something looks wrong. Each section is gathered on its own, so one failing check
 * reports its error instead of hiding the rest.
 */
class SiteHealth extends Ability
{
    public function name(): string
    {
        return 'alma/site-health';
    }

    public function label(): string
    {
        return __('Site health', 'alma');
    }

    public function description(): string
    {
        return __('Report the running release, environment, PHP and WordPress versions, active plugins and modules, WP-Cron health and the site\'s health checks (database, mail, error reporting, queue). Read-only.', 'alma');
    }

    public function category(): string
    {
        return 'alma-site';
    }

    public function readOnly(): bool
    {
        return true;
    }

    public function idempotent(): bool
    {
        return true;
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_pages');
    }

    public function execute(array $input): array
    {
        return [
            'site' => $this->section(fn () => [
                'name' => get_bloginfo('name'),
                'url' => home_url('/'),
                'release' => (string) config('alma.version', 'dev'),
                'environment' => wp_get_environment_type(),
                'php' => PHP_VERSION,
                'wordpress' => get_bloginfo('version'),
            ]),
            'modules' => $this->section(fn () => $this->modules()),
            'plugins' => $this->section(fn () => array_values(array_map(
                fn (string $file) => dirname($file) !== '.' ? dirname($file) : basename($file, '.php'),
                (array) get_option('active_plugins', []),
            ))),
            'cron' => $this->section(fn () => class_exists(CronHealth::class)
                ? app(CronHealth::class)->assess()
                : ['status' => 'unknown']),
            'checks' => $this->section(fn () => $this->checks()),
        ];
    }

    /**
     * @return list<string>
     */
    private function modules(): array
    {
        $loaded = array_keys(app()->getLoadedProviders());

        return array_keys(array_filter(
            (array) config('alma.modules', []),
            fn (string $provider) => in_array($provider, $loaded, true),
        ));
    }

    /**
     * @return array<string, array{status: string, summary: string}>
     */
    private function checks(): array
    {
        if (! class_exists(HealthChecker::class)) {
            return [];
        }

        $results = [];

        foreach (app(HealthChecker::class)->run() as $name => $result) {
            $results[$name] = ['status' => $result->status, 'summary' => $result->summary];
        }

        return $results;
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function section(Closure $gather): array
    {
        try {
            return (array) $gather();
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

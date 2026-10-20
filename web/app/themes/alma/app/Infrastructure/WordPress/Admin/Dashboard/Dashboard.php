<?php

namespace App\Infrastructure\WordPress\Admin\Dashboard;

use App\Infrastructure\Providers\ModuleServiceProvider;
use ReflectionClass;
use Throwable;

/**
 * The wp-admin dashboard: core's blog widgets out, the site's own in.
 *
 * Two columns. Modules put their business widgets in the left one (Leads adds its KPIs, hourly
 * trend, channels and latest leads); this class puts the platform in the right one: the modules
 * and their status, health and integrations, and shortcuts. Widget markup is in
 * resources/views/admin/dashboard; each render method gathers what its widget shows and hands it
 * to the view.
 *
 * Modules contribute through two filters: `alma/dashboard/modules` (live figures on a module's
 * card, as `chips`) and `alma/dashboard/shortcuts`.
 */
class Dashboard
{
    /** Core's widgets that a marketing site's team never acts on. */
    private const CORE_WIDGETS = [
        ['dashboard_quick_press', 'side'],
        ['dashboard_primary', 'side'],
        ['dashboard_secondary', 'side'],
        ['dashboard_recent_drafts', 'side'],
        ['dashboard_activity', 'normal'],
        ['dashboard_right_now', 'normal'],
        ['dashboard_site_health', 'normal'],
        ['dashboard_recent_comments', 'normal'],
        ['dashboard_incoming_links', 'normal'],
        ['dashboard_plugins', 'normal'],
    ];

    public function __construct(private readonly PlatformHealth $health) {}

    public function register(): void
    {
        add_action('wp_dashboard_setup', [$this, 'setup'], 999);
    }

    public function setup(): void
    {
        foreach (self::CORE_WIDGETS as [$id, $context]) {
            remove_meta_box($id, 'dashboard', $context);
        }

        // The welcome panel would sit above both columns on a fresh install.
        remove_action('welcome_panel', 'wp_welcome_panel');

        wp_add_dashboard_widget('alma_dashboard_modules', __('Modules & Services Status', 'alma'), [$this, 'renderModules'], null, null, 'side', 'high');

        if (current_user_can('manage_options')) {
            wp_add_dashboard_widget('alma_dashboard_site_health', __('Platform & Infrastructure Health', 'alma'), [$this, 'renderHealth'], null, null, 'side', 'default');
        }

        wp_add_dashboard_widget('alma_dashboard_shortcuts', __('Shortcuts & Quick Actions', 'alma'), [$this, 'renderShortcuts'], null, null, 'side', 'low');
    }

    public function renderModules(): void
    {
        echo view('admin.dashboard.modules', ['modules' => $this->modules()])->render();
    }

    public function renderHealth(): void
    {
        echo view('admin.dashboard.site-health', $this->health->snapshot())->render();
    }

    public function renderShortcuts(): void
    {
        echo view('admin.dashboard.shortcuts', ['shortcuts' => $this->shortcuts()])->render();
    }

    /**
     * Every module in config/alma.php: whether it runs here, what it is (its provider's summary),
     * what it declares, and whatever live figures it adds through `alma/dashboard/modules`.
     *
     * @return array<string, array{title: string, status: string, description: string, chips: list<string>}>
     */
    public function modules(): array
    {
        $disabled = (array) config('alma.disabled', []);
        $modules = [];

        foreach ((array) config('alma.modules', []) as $key => $provider) {
            $status = match (true) {
                in_array($key, $disabled, true) => 'disabled',
                ! is_a($provider, ModuleServiceProvider::class, true) || ! $provider::enabled() => 'unavailable',
                default => 'active',
            };

            $modules[$key] = [
                'title' => ucfirst((string) $key),
                'status' => $status,
                'description' => self::summary($provider),
                'chips' => self::declared($provider),
            ];
        }

        $filtered = apply_filters('alma/dashboard/modules', $modules);

        return is_array($filtered) ? $filtered : $modules;
    }

    /**
     * The quick actions: core's first, then each module's (`alma/dashboard/shortcuts`).
     *
     * @return list<array{title: string, description: string, url: string, icon: string, external?: bool}>
     */
    public function shortcuts(): array
    {
        $shortcuts = [
            [
                'title' => __('View live website', 'alma'),
                'description' => __('Open the site in a new tab', 'alma'),
                'url' => home_url('/'),
                'icon' => '<polygon points="5 3 19 12 5 21 5 3"/>',
                'external' => true,
            ],
            [
                'title' => __('Add a page', 'alma'),
                'description' => __('Start a new page from a pattern', 'alma'),
                'url' => admin_url('post-new.php?post_type=page'),
                'icon' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="12" x2="12" y1="18" y2="12"/><line x1="9" x2="15" y1="15" y2="15"/>',
            ],
        ];

        if (current_user_can('manage_options')) {
            $shortcuts[] = [
                'title' => __('Integration log', 'alma'),
                'description' => __('Every outbound call and its answer', 'alma'),
                'url' => admin_url('tools.php?page=alma-integration-log'),
                'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            ];
            $shortcuts[] = [
                'title' => __('Site Health', 'alma'),
                'description' => __('Every check, with what to do about it', 'alma'),
                'url' => admin_url('site-health.php'),
                'icon' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            ];
        }

        $filtered = apply_filters('alma/dashboard/shortcuts', $shortcuts);

        return is_array($filtered) ? array_values($filtered) : $shortcuts;
    }

    /**
     * The first paragraph of the provider's docblock, which is what each module says it is.
     */
    private static function summary(string $provider): string
    {
        try {
            $doc = (string) (new ReflectionClass($provider))->getDocComment();
        } catch (Throwable) {
            return '';
        }

        $lines = array_map(fn (string $line) => trim(ltrim(trim($line), '/*')), explode("\n", $doc));
        $paragraph = [];

        foreach ($lines as $line) {
            if ($line === '' && $paragraph !== []) {
                break;
            }

            if ($line !== '' && ! str_starts_with($line, '@')) {
                $paragraph[] = $line;
            }
        }

        return implode(' ', $paragraph);
    }

    /**
     * What the provider declares, counted: the module's shape at a glance.
     *
     * @return list<string>
     */
    private static function declared(string $provider): array
    {
        try {
            $defaults = (new ReflectionClass($provider))->getDefaultProperties();
        } catch (Throwable) {
            return [];
        }

        $count = fn (string $property) => is_array($defaults[$property] ?? null) ? count($defaults[$property]) : 0;
        $listeners = $count('listen') + $count('listenAfterResponse');

        return array_values(array_filter([
            $count('hooks') > 0 ? sprintf(_n('%d hook', '%d hooks', $count('hooks'), 'alma'), $count('hooks')) : null,
            $listeners > 0 ? sprintf(_n('%d event', '%d events', $listeners, 'alma'), $listeners) : null,
            $count('commands') > 0 ? sprintf(_n('%d command', '%d commands', $count('commands'), 'alma'), $count('commands')) : null,
            $count('deployTasks') > 0 ? sprintf(_n('%d deploy task', '%d deploy tasks', $count('deployTasks'), 'alma'), $count('deployTasks')) : null,
            ! empty($defaults['webRoutes']) || ! empty($defaults['apiRoutes']) ? __('Routes', 'alma') : null,
            ! empty($defaults['migrations']) ? __('Tables', 'alma') : null,
        ]));
    }
}

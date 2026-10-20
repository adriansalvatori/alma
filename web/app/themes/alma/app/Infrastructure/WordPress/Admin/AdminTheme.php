<?php

namespace App\Infrastructure\WordPress\Admin;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\WordPress\Admin\Dashboard\PlatformHealth;

/**
 * The admin's look: wp-admin, the toolbar on both sides of the site, and wp-login.php, in the
 * shadcn/ui zinc console Remote Leverage's admin was built with, branded from config/admin.php.
 * ADMIN_THEME=false leaves WordPress' own look and registers nothing.
 *
 * The sheets (resources/css/admin/) are enqueued as hashed <link>s that depend on core's own
 * handles, so they print after them. Nearly every rule is !important, as it was in the original,
 * so the order the sheets import their parts in (skin.css) is what settles ties.
 *
 * The toolbar's notifications bell collects wp-admin's notices into a drawer
 * (admin/_notifications.blade.php) instead of stacking them above every screen. A notice that
 * must stay on the page carries the `alma-keep-notice` class (the stalled-cron notice does).
 */
class AdminTheme
{
    public const SKIN = 'resources/css/admin/skin.css';

    public const TOOLBAR = 'resources/css/admin/toolbar.css';

    public const LOGIN = 'resources/css/admin/login.css';

    /** The toolbar nodes a client site doesn't need: core's menus that duplicate the sidebar. */
    private const NOISE = ['wp-logo', 'comments', 'view-site', 'dashboard', 'themes', 'widgets', 'menus', 'customize', 'search'];

    public function __construct(
        private readonly Brand $brand,
        private readonly AdminStyles $styles,
    ) {}

    public function register(): void
    {
        if (! config('admin.theme', true)) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueueSkin'], 99);
        add_action('login_enqueue_scripts', [$this, 'enqueueLogin'], 99);
        add_action('wp_enqueue_scripts', [$this, 'enqueueToolbar'], 99);
        add_action('wp_head', [$this, 'ensureToolbarInitialized'], 0);
        add_action('admin_bar_menu', [$this, 'brandToolbar'], 999);
        add_action('admin_footer', [$this, 'renderNotifications'], 999);
        add_action('wp_footer', [$this, 'renderSiteNotifications'], 999);

        add_filter('login_headerurl', [$this, 'loginUrl']);
        add_filter('login_headertext', [$this, 'loginText']);
        add_filter('admin_footer_text', [$this, 'footerText']);
        add_filter('update_footer', [$this, 'footerVersion'], 99);
        add_filter('acf/settings/show_admin', [$this, 'showAcfAdmin']);
    }

    public function enqueueSkin(): void
    {
        // After the core sheets and the user's colour scheme ("colors").
        $this->styles->enqueue('alma-admin-skin', self::SKIN, ['wp-admin', 'colors']);
    }

    public function enqueueLogin(): void
    {
        $this->styles->enqueue('alma-admin-login', self::LOGIN, ['login']);
    }

    /**
     * The front-end toolbar, for logged-in users only. In wp-admin the skin carries it.
     */
    public function enqueueToolbar(): void
    {
        if (is_admin_bar_showing()) {
            $this->styles->enqueue('alma-admin-toolbar', self::TOOLBAR, ['admin-bar']);
        }
    }

    /**
     * Initialise the toolbar on Acorn-routed pages.
     *
     * WordPress builds the toolbar on `template_redirect`. Acorn answers its routes (the account
     * area, for example) on `parse_request` and never reaches that hook, so on those pages
     * $wp_admin_bar stays null: core prints no toolbar, yet is_admin_bar_showing() is true, body
     * gets the `admin-bar` class, and a 32px empty band appears at the top. Remote Leverage hit
     * exactly this on every route page.
     *
     * Priority 0 on wp_head: early enough that the margin-bump callback core registers at wp_head
     * 10 still runs, and long before wp_body_open renders the bar. An ordinary WordPress request
     * initialised the bar at template_redirect and falls straight through.
     */
    public function ensureToolbarInitialized(): void
    {
        if (! is_admin_bar_showing() || is_object($GLOBALS['wp_admin_bar'] ?? null)) {
            return;
        }

        if (function_exists('_wp_admin_bar_init')) {
            _wp_admin_bar_init();
        }
    }

    /**
     * One brand on the left with the places people go from it, the notifications bell on the
     * right, and none of core's menus that repeat the sidebar. Modules add their own entries
     * under `site-name` (Leads adds "Leads & submissions").
     */
    public function brandToolbar(\WP_Admin_Bar $toolbar): void
    {
        foreach (self::NOISE as $node) {
            $toolbar->remove_node($node);
        }

        $toolbar->add_node([
            'id' => 'site-name',
            'title' => $this->brand->html(),
            'href' => admin_url('/'),
            'meta' => ['title' => sprintf(__('%s admin', 'alma'), $this->brand->name())],
        ]);

        $toolbar->add_node([
            'id' => 'alma-sub-dashboard',
            'parent' => 'site-name',
            'title' => esc_html__('Admin console', 'alma'),
            'href' => admin_url('/'),
        ]);

        $toolbar->add_node([
            'id' => 'alma-sub-site',
            'parent' => 'site-name',
            'title' => esc_html__('View live website', 'alma'),
            'href' => home_url('/'),
        ]);

        $toolbar->add_node([
            'id' => 'alma-notifications',
            'parent' => 'top-secondary',
            'title' => '<span class="alma-notif-bar-trigger" id="alma-notif-trigger" role="button" tabindex="0" title="'.esc_attr__('Notifications', 'alma').'">'
                .'<svg class="alma-notif-bell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>'
                .'<span class="alma-notif-badge" id="alma-notif-badge" style="display:none;">0</span>'
                .'</span>',
            'href' => '#',
            'meta' => ['title' => __('Notifications', 'alma'), 'class' => 'alma-notif-admin-bar-item'],
        ]);
    }

    /**
     * The notifications drawer and the script that moves wp-admin's notices into it.
     */
    public function renderNotifications(): void
    {
        echo view('admin._notifications', ['brand' => $this->brand->name()])->render();
    }

    /**
     * The same drawer on the front end, wherever the toolbar (and so its bell) shows.
     */
    public function renderSiteNotifications(): void
    {
        if (is_admin_bar_showing()) {
            $this->renderNotifications();
        }
    }

    public function loginUrl(): string
    {
        return home_url('/');
    }

    /**
     * The login header's link text, which core prints unescaped: the mark and the escaped name.
     */
    public function loginText(): string
    {
        return $this->brand->html();
    }

    /**
     * "<Brand> Admin Console", then how the health checks stood the last time the dashboard ran
     * them. Only a cached result is read, so the footer never runs a check itself, and it says
     * nothing rather than claim a state it hasn't seen.
     */
    public function footerText(): string
    {
        $text = '<span class="alma-footer-brand" style="font-weight: 500; color: #71717a;">'
            .esc_html(sprintf(__('%s Admin Console', 'alma'), $this->brand->name())).'</span>';

        $overall = PlatformHealth::cachedOverall();

        if ($overall !== null) {
            $text .= ' &bull; <span style="color: #a1a1aa;">'.esc_html($overall === CheckResult::OK
                ? __('All systems operational', 'alma')
                : __('Some checks need attention', 'alma')).'</span>';
        }

        return $text;
    }

    /**
     * Which release is live, where people look for a version: the right side of the footer.
     */
    public function footerVersion(): string
    {
        return '<span style="color: #a1a1aa; font-size: 11px;">'.esc_html(sprintf(
            /* translators: 1: release (APP_VERSION), 2: WordPress version */
            __('Release %1$s · WordPress %2$s', 'alma'),
            config('alma.version', 'dev'),
            get_bloginfo('version'),
        )).'</span>';
    }

    public function showAcfAdmin(): bool
    {
        $setting = config('admin.acf_ui');

        return $setting === null ? wp_get_environment_type() !== 'production' : (bool) $setting;
    }
}

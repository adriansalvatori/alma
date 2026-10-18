<?php

namespace App\Infrastructure\WordPress\Admin;

/**
 * The admin's look: wp-admin, the toolbar on both sides of the site, and wp-login.php, branded
 * from the design tokens (resources/css/tokens.css) and config/admin.php. ADMIN_THEME=false
 * leaves WordPress' own look and registers nothing.
 *
 * The sheets (resources/css/admin/) are enqueued as hashed <link>s that depend on core's own
 * handles, so they print after them. Remote Leverage printed its skin inline at an exact point
 * because nearly every rule was !important and ties were settled by order. These sheets
 * outrank core by specificity instead (each selector is core's with `body.wp-admin` or `html` in
 * front), so their position no longer decides anything, and they can be cached files.
 */
class AdminTheme
{
    public const SKIN = 'resources/css/admin/skin.css';

    public const TOOLBAR = 'resources/css/admin/toolbar.css';

    public const LOGIN = 'resources/css/admin/login.css';

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
     * Put the brand on the site-name node (core keeps its link and menu), and drop the WordPress
     * logo menu and the toolbar search, which a client site doesn't need.
     */
    public function brandToolbar(\WP_Admin_Bar $toolbar): void
    {
        $toolbar->remove_node('wp-logo');
        $toolbar->remove_node('search');

        if ($toolbar->get_node('site-name')) {
            $toolbar->add_node([
                'id' => 'site-name',
                'title' => $this->brand->html(),
            ]);
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

    public function footerText(): string
    {
        return esc_html($this->brand->name());
    }

    /**
     * Which release is live, where people look for a version: the right side of the footer.
     */
    public function footerVersion(): string
    {
        return esc_html(sprintf(
            /* translators: 1: release (APP_VERSION), 2: WordPress version */
            __('Release %1$s · WordPress %2$s', 'alma'),
            config('alma.version', 'dev'),
            get_bloginfo('version'),
        ));
    }

    public function showAcfAdmin(): bool
    {
        $setting = config('admin.acf_ui');

        return $setting === null ? wp_get_environment_type() !== 'production' : (bool) $setting;
    }
}

<?php

namespace App\Infrastructure\WordPress\Admin;

use Illuminate\Support\Facades\Blade;

/**
 * The design system alma's own wp-admin screens are built from.
 *
 * Two parts: the <x-admin::…> Blade components (resources/views/admin/components) and the sheet
 * that styles them (resources/css/admin/screens.css, which brings the dashboard widgets' sheet
 * with it). Everything in it is scoped under .alma-admin, the wrapper <x-admin::page> renders, or
 * prefixed alma-dash-, so it loads on every admin screen without touching core ones. It prints
 * after the skin and doesn't depend on it: with ADMIN_THEME=false a module's screens and the
 * dashboard widgets still look like one product.
 *
 * See docs/design-system.md ("Admin") for the components.
 */
class AdminDesignSystem
{
    public const STYLESHEET = 'resources/css/admin/screens.css';

    public function __construct(private readonly AdminStyles $styles) {}

    public function register(): void
    {
        // Not a WordPress hook, but this is where the screens' building blocks are wired up.
        Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        add_action('admin_enqueue_scripts', [$this, 'enqueue'], 100);
    }

    public function enqueue(): void
    {
        $this->styles->enqueue('alma-admin-screens', self::STYLESHEET, ['wp-admin']);
    }
}

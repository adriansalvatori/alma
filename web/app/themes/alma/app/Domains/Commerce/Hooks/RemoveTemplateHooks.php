<?php

namespace App\Domains\Commerce\Hooks;

/**
 * Unhook the WooCommerce template hooks listed in config/commerce.php.
 *
 * Replaces Sage's commented-out app/wc-template-hooks.php: the list lives in config, so a
 * project changes what WooCommerce prints without editing code. Runs on `wp`, after WooCommerce
 * has attached its hooks and before any template renders.
 */
class RemoveTemplateHooks
{
    public function register(): void
    {
        add_action('wp', [$this, 'remove'], 0);
    }

    public function remove(): void
    {
        foreach ((array) config('commerce.remove_hooks', []) as $hook) {
            [$name, $callback, $priority] = array_pad((array) $hook, 3, 10);

            if (is_string($name) && is_callable($callback)) {
                remove_action($name, $callback, (int) $priority);
            }
        }
    }
}

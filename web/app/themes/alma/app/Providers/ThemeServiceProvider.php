<?php

namespace App\Providers;

use App\Infrastructure\Providers\CoreServiceProvider;
use App\Infrastructure\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register the core infrastructure, then every enabled module in config/alma.php.
     */
    public function register()
    {
        parent::register();

        $this->app->register(CoreServiceProvider::class);

        foreach ($this->enabledModules() as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot()
    {
        parent::boot();

        // Blade views that use validation (Flux inputs read $errors) are also rendered by
        // WordPress, where the web middleware that shares the session's errors never runs.
        View::share('errors', new ViewErrorBag);

        $this->ensureApplicationTerminates();
    }

    /**
     * The module providers to register, in config order.
     *
     * A module is skipped when its key is listed in ALMA_DISABLED_MODULES, or when its provider's
     * enabled() says its prerequisite is missing (Commerce without WooCommerce, for example).
     *
     * @return list<class-string<ModuleServiceProvider>>
     */
    public function enabledModules(): array
    {
        $disabled = config('alma.disabled', []);

        return collect(config('alma.modules', []))
            ->reject(fn (string $provider, string $key) => in_array($key, $disabled, true))
            ->filter(fn (string $provider) => $provider::enabled())
            ->values()
            ->all();
    }

    /**
     * Run the container's terminating callbacks on requests Acorn does not route itself.
     *
     * Every deferred listener goes through App\Infrastructure\Queue\Deferred. With no queue
     * configured (the shared-hosting default) that is dispatchAfterResponse(), which registers a
     * terminating callback, and a terminating callback only runs if something calls
     * $app->terminate().
     *
     * Acorn only terminates on the routes it handles itself. A page rendered by the WordPress
     * template hierarchy, wp-admin, wp-login.php and wp-json never terminate, so every deferred
     * job would be discarded with no error and no log line. Remote Leverage measured exactly that
     * in production (2026-09-18) before adding this hook.
     *
     * The guard matters: terminate() walks its callbacks without clearing them, so a second call
     * re-runs every job (a duplicate notification email, a duplicate webhook). On the paths Acorn
     * does handle it terminates and then exits, which still reaches WordPress' shutdown hook, so
     * the sentinel below is what stops this callback being that second call.
     */
    protected function ensureApplicationTerminates(): void
    {
        if ($this->app->runningInConsole() || ! function_exists('add_action')) {
            return;
        }

        $state = new \stdClass;
        $state->terminated = false;

        $this->app->terminating(static function () use ($state) {
            $state->terminated = true;
        });

        add_action('shutdown', function () use ($state) {
            if ($state->terminated) {
                return;
            }

            // Hand the response to the visitor first, so they never wait on deferred work. Under
            // mod_php neither function exists and the work still runs, but the browser waits.
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } elseif (function_exists('litespeed_finish_request')) {
                litespeed_finish_request();
            }

            $this->app->terminate();
        }, 999);
    }
}

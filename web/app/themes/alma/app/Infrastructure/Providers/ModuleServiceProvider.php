<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Deploy\DeployTask;
use App\Infrastructure\Queue\Deferred;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Base class for a module (a folder under app/Domains with its own provider).
 *
 * A module declares what it contributes instead of wiring it by hand, so the whole hook and event
 * graph of a module can be read from the top of one file:
 *
 * - $hooks: classes with a register() method that only adds WordPress hooks. Resolved from the
 *   container and registered in boot().
 * - $listen: event => listeners that run synchronously, inside the request. Use for database-only
 *   work that must be done before the response.
 * - $listenAfterResponse: event => listeners that run after the response (or on a queue, when
 *   one is configured). Use for anything that calls a third party.
 * - $commands: wp acorn commands, registered only in the console.
 * - $deployTasks: steps contributed to `wp acorn alma:deploy`.
 * - $migrations: a migrations directory, picked up by `wp acorn migrate`.
 *
 * A listener is a class name (its handle() method is called) or [class, method].
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** @var list<class-string> */
    protected array $hooks = [];

    /** @var array<class-string, list<class-string|array{class-string, string}>> */
    protected array $listen = [];

    /** @var array<class-string, list<class-string|array{class-string, string}>> */
    protected array $listenAfterResponse = [];

    /** @var list<class-string> */
    protected array $commands = [];

    /** @var list<class-string<DeployTask>> */
    protected array $deployTasks = [];

    protected ?string $migrations = null;

    /**
     * Whether the module's prerequisites are present. Checked before the provider is registered,
     * so a module can depend on a plugin (WooCommerce) or a core API without guarding every call.
     */
    public static function enabled(): bool
    {
        return true;
    }

    public function register(): void
    {
        if ($this->deployTasks !== []) {
            $this->app->tag($this->deployTasks, DeployTask::TAG);
        }

        if ($this->commands !== [] && $this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    public function boot(): void
    {
        if ($this->migrations !== null) {
            $this->loadMigrationsFrom($this->migrations);
        }

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $this->callable($listener));
            }
        }

        foreach ($this->listenAfterResponse as $event => $listeners) {
            foreach ($listeners as $listener) {
                [$class, $method] = $this->target($listener);

                Event::listen($event, static fn (object $event) => Deferred::call($class, $method, [$event]));
            }
        }

        foreach ($this->hooks as $hook) {
            $this->app->make($hook)->register();
        }
    }

    /**
     * @param  class-string|array{class-string, string}  $listener
     */
    protected function callable(string|array $listener): \Closure
    {
        [$class, $method] = $this->target($listener);

        return fn (object $event) => $this->app->make($class)->{$method}($event);
    }

    /**
     * @param  class-string|array{class-string, string}  $listener
     * @return array{class-string, string}
     */
    protected function target(string|array $listener): array
    {
        return is_array($listener) ? $listener : [$listener, 'handle'];
    }
}

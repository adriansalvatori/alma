<?php

namespace App\Infrastructure\Queue;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Run work after the response, with or without a queue worker.
 *
 * Shared hosting has no long-running worker, so the default (QUEUE_CONNECTION=sync) runs the job
 * after the response has been sent to the visitor: a slow CRM or webhook call never sits on the
 * page load. Setting QUEUE_CONNECTION=database moves the same jobs onto the jobs table, drained by
 * a cron running `wp acorn queue:work --stop-when-empty`. Nothing else changes.
 *
 * The after-response path depends on ThemeServiceProvider::ensureApplicationTerminates().
 */
final class Deferred
{
    /**
     * Call $handler->$method(...$arguments) later. The handler is resolved from the container.
     *
     * @param  class-string  $handler
     * @param  array<int, mixed>  $arguments
     */
    public static function call(string $handler, string $method, array $arguments = []): void
    {
        self::dispatch(new CallHandlerJob($handler, $method, $arguments));
    }

    public static function dispatch(ShouldQueue $job): void
    {
        $bus = app(Dispatcher::class);

        if (config('queue.default', 'sync') !== 'sync') {
            $bus->dispatch($job);

            return;
        }

        $bus->dispatchAfterResponse($job);
    }
}

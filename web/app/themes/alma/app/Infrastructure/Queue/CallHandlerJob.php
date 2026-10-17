<?php

namespace App\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A deferred method call: resolve $handler from the container and call $method on it.
 *
 * One attempt by default, because most handlers are not idempotent (a second attempt sends a
 * second email). A handler that is safe to retry gets its own subclass with a higher $tries and
 * a $backoff.
 */
class CallHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /**
     * @param  class-string  $handler
     * @param  array<int, mixed>  $arguments
     */
    public function __construct(
        public readonly string $handler,
        public readonly string $method,
        public readonly array $arguments = [],
    ) {}

    public function handle(): void
    {
        app($this->handler)->{$this->method}(...$this->arguments);
    }

    /**
     * The name shown in failed_jobs and logs, e.g. "SendLeadWebhook::handle".
     */
    public function displayName(): string
    {
        return class_basename($this->handler).'::'.$this->method;
    }

    public function failed(Throwable $e): void
    {
        Log::error('Deferred call failed: '.$this->displayName(), [
            'handler' => $this->handler,
            'method' => $this->method,
            'error' => $e->getMessage(),
        ]);
    }
}

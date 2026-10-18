<?php

namespace App\Infrastructure\Observability\IntegrationLog;

use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Throwable;

/**
 * Records outbound HTTP calls to alma_integration_calls: Laravel's Http client through its
 * events, WordPress' wp_remote_* through http_api_debug.
 *
 * The question it answers is "did the lead reach the CRM, and what did the CRM say?", from
 * wp-admin, months later, without a shell or a log aggregator. Everything stored is redacted
 * first, and recording never throws into the call it records.
 */
class IntegrationCallRecorder
{
    public function __construct(private readonly Redactor $redactor) {}

    public function register(): void
    {
        if (! config('observability.integration_log.enabled', true)) {
            return;
        }

        Event::listen(ResponseReceived::class, fn (ResponseReceived $event) => $this->recordLaravel($event->request, $event->response));
        Event::listen(ConnectionFailed::class, fn (ConnectionFailed $event) => $this->recordLaravel($event->request, null, $event->exception->getMessage()));

        add_action('http_api_debug', [$this, 'recordWordPress'], 10, 5);
    }

    public function recordLaravel(Request $request, ?Response $response, ?string $error = null): void
    {
        $stats = $response?->handlerStats() ?? [];

        $this->record([
            'source' => 'laravel',
            'method' => $request->method(),
            'url' => $request->url(),
            'status' => $response?->status(),
            'duration_ms' => isset($stats['total_time']) ? (int) round((float) $stats['total_time'] * 1000) : null,
            'request_headers' => array_map(fn ($values) => implode(', ', (array) $values), $request->headers()),
            'request_body' => $request->body(),
            'response_body' => $response?->body(),
            'error' => $error,
        ]);
    }

    /**
     * @param  array<string, mixed>|\WP_Error  $response
     * @param  array<string, mixed>  $args
     */
    public function recordWordPress(mixed $response, string $context, string $class, array $args, string $url): void
    {
        if ($context !== 'response') {
            return;
        }

        $error = $response instanceof \WP_Error ? $response->get_error_message() : null;
        $headers = (array) ($args['headers'] ?? []);

        $this->record([
            'source' => 'wordpress',
            'method' => strtoupper((string) ($args['method'] ?? 'GET')),
            'url' => $url,
            'status' => is_array($response) ? (int) ($response['response']['code'] ?? 0) ?: null : null,
            'duration_ms' => null,
            'request_headers' => $headers,
            'request_body' => is_string($args['body'] ?? null) ? $args['body'] : (is_array($args['body'] ?? null) ? http_build_query($args['body']) : null),
            'response_body' => is_array($response) ? (string) ($response['body'] ?? '') : null,
            'error' => $error,
        ]);
    }

    /**
     * @param  array{source: string, method: string, url: string, status: int|null, duration_ms: int|null, request_headers: array<string, mixed>, request_body: string|null, response_body: string|null, error: string|null}  $call
     */
    public function record(array $call): void
    {
        try {
            $host = strtolower((string) parse_url($call['url'], PHP_URL_HOST));

            if (! $this->shouldRecord($host)) {
                return;
            }

            $max = (int) config('observability.integration_log.max_body_bytes', 65536);

            IntegrationCall::query()->create([
                'source' => $call['source'],
                'method' => substr($call['method'], 0, 10),
                'host' => substr($host, 0, 255),
                'url' => substr($this->redactor->url($call['url']), 0, 2048),
                'status' => $call['status'],
                'duration_ms' => $call['duration_ms'],
                'request_headers' => $this->redactor->headers($call['request_headers']),
                'request_body' => $this->redactor->body($call['request_body'], $max),
                'response_body' => $this->redactor->body($call['response_body'], $max),
                'error' => $call['error'] !== null ? substr($call['error'], 0, 1000) : null,
            ]);
        } catch (Throwable) {
            // The table may not exist yet, or the database may be the thing failing. Either way
            // the integration call itself already happened and must not be affected.
        }
    }

    public function shouldRecord(string $host): bool
    {
        // The site calling itself (WP-Cron spawning, Site Health's loopback checks) is not an
        // integration, and "localhost" in ignore_hosts never matches the site's real hostname.
        if ($host === '' || in_array($host, $this->ownHosts(), true)) {
            return false;
        }

        $allow = (array) config('observability.integration_log.hosts', []);

        if ($allow !== [] && ! $this->matchesAny($host, $allow)) {
            return false;
        }

        return ! $this->matchesAny($host, (array) config('observability.integration_log.ignore_hosts', []));
    }

    /**
     * @return list<string>
     */
    private function ownHosts(): array
    {
        return array_values(array_unique(array_filter([
            strtolower((string) parse_url(home_url(), PHP_URL_HOST)),
            strtolower((string) parse_url(site_url(), PHP_URL_HOST)),
        ])));
    }

    /**
     * @param  list<string>  $patterns
     */
    private function matchesAny(string $host, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = strtolower(trim((string) $pattern));

            if ($pattern === $host || (str_starts_with($pattern, '*.') && str_ends_with($host, substr($pattern, 1)))) {
                return true;
            }
        }

        return false;
    }
}

<?php

use App\Infrastructure\Cache\SoftCache;
use App\Infrastructure\Observability\IntegrationLog\IntegrationCall;
use App\Infrastructure\Observability\IntegrationLog\IntegrationCallRecorder;
use App\Infrastructure\Observability\IntegrationLog\PruneIntegrationCalls;
use App\Infrastructure\Observability\IntegrationLog\Redactor;
use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

describe('integration call log', function () {
    it('records a Laravel Http call with secrets redacted', function () {
        Http::fake(['crm.example.com/*' => Http::response(['id' => 42, 'access_token' => 'tok-123'], 201)]);

        Http::withToken('super-secret')
            ->withHeaders(['X-Api-Key' => 'k-1', 'X-Request-Id' => 'abc'])
            ->post('https://crm.example.com/contacts?api_key=k-2&list=7', ['email' => 'a@b.test', 'password' => 'hunter2']);

        $call = IntegrationCall::query()->sole();

        expect($call)
            ->source->toBe('laravel')
            ->method->toBe('POST')
            ->host->toBe('crm.example.com')
            ->status->toBe(201)
            ->and($call->url)->toBe('https://crm.example.com/contacts?api_key=%5Bredacted%5D&list=7')
            ->and($call->request_headers['Authorization'])->toBe('[redacted]')
            ->and($call->request_headers['X-Api-Key'])->toBe('[redacted]')
            ->and($call->request_headers['X-Request-Id'])->toBe('abc')
            ->and($call->request_body)->toContain('a@b.test')->not->toContain('hunter2')
            ->and($call->response_body)->toContain('"id":42')->not->toContain('tok-123');
    });

    it('records failed connections', function () {
        $request = new Illuminate\Http\Client\Request(new Request('GET', 'https://down.example.com/ping'));

        app(IntegrationCallRecorder::class)->recordLaravel($request, null, 'cURL error 6: Could not resolve host');

        $call = IntegrationCall::query()->sole();

        expect($call->status)->toBeNull()
            ->and($call->error)->toContain('Could not resolve host')
            ->and($call->succeeded())->toBeFalse();
    });

    it('listens for connection failures from the Http client', function () {
        expect(Event::hasListeners(ConnectionFailed::class))->toBeTrue()
            ->and(Event::hasListeners(ResponseReceived::class))->toBeTrue();
    });

    it('records WordPress wp_remote_* calls', function () {
        $response = ['response' => ['code' => 500], 'body' => '{"error":"boom"}', 'headers' => []];

        do_action('http_api_debug', $response, 'response', 'WpOrg\Requests\Requests', ['method' => 'POST', 'headers' => ['Authorization' => 'Bearer x'], 'body' => '{"a":1}'], 'https://hooks.example.com/lead');

        expect(IntegrationCall::query()->sole())
            ->source->toBe('wordpress')
            ->status->toBe(500)
            ->and(IntegrationCall::query()->sole()->request_headers['Authorization'])->toBe('[redacted]');
    });

    it('skips ignored hosts and honours the allowlist', function () {
        $recorder = app(IntegrationCallRecorder::class);

        expect($recorder->shouldRecord('api.wordpress.org'))->toBeFalse()
            ->and($recorder->shouldRecord('o12.ingest.sentry.io'))->toBeFalse()
            ->and($recorder->shouldRecord('crm.example.com'))->toBeTrue();

        config(['observability.integration_log.hosts' => ['*.hubapi.com']]);

        expect($recorder->shouldRecord('api.hubapi.com'))->toBeTrue()
            ->and($recorder->shouldRecord('crm.example.com'))->toBeFalse();
    });

    it('caps stored bodies', function () {
        expect(strlen((new Redactor(['secret']))->body(str_repeat('a', 70000), 65536)))->toBeLessThan(65600);
    });

    it('never fails the call it records, even without its table', function () {
        Schema::drop('alma_integration_calls');
        Http::fake(['*' => Http::response('ok')]);

        expect(Http::get('https://crm.example.com/x')->body())->toBe('ok');
    });

    it('prunes calls past the retention window and schedules itself daily', function () {
        IntegrationCall::query()->create(['source' => 'laravel', 'method' => 'GET', 'host' => 'a.test', 'url' => 'https://a.test', 'created_at' => now()->subDays(40)]);
        IntegrationCall::query()->create(['source' => 'laravel', 'method' => 'GET', 'host' => 'a.test', 'url' => 'https://a.test', 'created_at' => now()->subDays(2)]);

        expect(app(PruneIntegrationCalls::class)->prune())->toBe(1)
            ->and(IntegrationCall::query()->count())->toBe(1);

        do_action('init');

        expect(WordPress::$cron[PruneIntegrationCalls::HOOK]['recurrence'])->toBe('daily');
    });
});

describe('soft cache', function () {
    it('treats a broken cache as a miss and keeps working', function () {
        $broken = Mockery::mock(Repository::class);
        $broken->shouldReceive('get')->andThrow(new RuntimeException('disk full'));
        $broken->shouldReceive('put')->andThrow(new RuntimeException('disk full'));

        $cache = new SoftCache($broken);

        expect($cache->get('k', 'fallback'))->toBe('fallback')
            ->and($cache->put('k', 1))->toBeFalse()
            ->and($cache->remember('k', 60, fn () => 'computed'))->toBe('computed');
    });

    it('behaves like the cache when it works', function () {
        $cache = new SoftCache(app('cache')->store());

        $cache->put('k', 'v', 60);

        expect($cache->get('k'))->toBe('v')
            ->and($cache->remember('k', 60, fn () => 'other'))->toBe('v');
    });
});

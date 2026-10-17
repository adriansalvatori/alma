<?php

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\Checks\CronCheck;
use App\Infrastructure\Observability\Health\CronHealth;
use App\Infrastructure\Observability\Health\HealthCheck;
use App\Infrastructure\Observability\Health\HealthChecker;
use App\Infrastructure\Observability\SiteHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

describe('cron health', function () {
    it('is ok when nothing is overdue', function () {
        $now = 1_800_000_000;

        expect((new CronHealth)->assess([$now + 60 => ['alma_task' => ['x' => []]]], $now)['status'])->toBe('ok');
    });

    it('is behind past the first threshold and stalled past the second', function () {
        $now = 1_800_000_000;
        $cron = new CronHealth;

        expect($cron->assess([$now - 600 => ['a' => ['x' => []]]], $now)['status'])->toBe(CronHealth::OK)
            ->and($cron->assess([$now - 901 => ['a' => ['x' => []]]], $now)['status'])->toBe(CronHealth::BEHIND)
            ->and($cron->assess([$now - 3601 => ['a' => ['x' => []], 'b' => ['y' => [], 'z' => []]]], $now))
            ->toMatchArray(['status' => CronHealth::STALLED, 'overdue_seconds' => 3601, 'overdue_events' => 3]);
    });

    it('measures from the oldest due event', function () {
        $now = 1_800_000_000;

        expect((new CronHealth)->assess([$now - 100 => ['a' => ['x' => []]], $now - 5000 => ['b' => ['y' => []]]], $now)['overdue_seconds'])->toBe(5000);
    });

    it('reports a stalled scheduler as critical', function () {
        wp_schedule_event(time() - 7200, 'daily', 'alma_task');

        expect(app(CronCheck::class)->run()->status)->toBe(CheckResult::CRITICAL);
    });
});

describe('endpoints', function () {
    it('answers liveness with the version, uncached and unindexed', function () {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJson(['status' => 'ok', 'version' => 'test'])
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertHeaderContains('Cache-Control', 'no-store');
    });

    it('shows anonymous callers only the status of each check', function () {
        $response = $this->getJson('/api/health/checks')->assertOk();

        expect($response->json('checks.database'))->toBe('ok')
            ->and($response->json('checks'))->toHaveKeys(['database', 'cron', 'failed_jobs', 'mail', 'error_reporting'])
            ->and(json_encode($response->json()))->not->toContain('latency_ms');
    });

    it('shows details with the health token', function () {
        config(['health.token' => 'let-me-in']);

        $this->getJson('/api/health/checks', ['X-Health-Token' => 'let-me-in'])
            ->assertOk()
            ->assertJsonStructure(['checks' => ['database' => ['status', 'summary', 'details' => ['latency_ms']]]]);
    });

    it('rejects a wrong token, and an empty configured token never matches', function () {
        config(['health.token' => 'let-me-in']);
        expect($this->getJson('/api/health/checks', ['X-Health-Token' => 'nope'])->json('checks.database'))->toBe('ok');

        config(['health.token' => '']);
        expect($this->getJson('/api/health/checks?token=')->json('checks.database'))->toBe('ok');
    });

    it('shows details to logged-in administrators', function () {
        $this->actingAsWordPressUser(['role' => 'administrator']);

        expect($this->getJson('/api/health/checks')->json('checks.database.status'))->toBe('ok');
    });

    it('returns 503 when a check is critical', function () {
        wp_schedule_event(time() - 7200, 'daily', 'alma_task');

        $this->getJson('/api/health/checks')->assertStatus(503)->assertJson(['status' => 'critical', 'checks' => ['cron' => 'critical']]);
    });

    it('reports a failing check as critical instead of failing the endpoint', function () {
        app()->bind('broken.check', fn () => new class implements HealthCheck
        {
            public function name(): string
            {
                return 'broken';
            }

            public function label(): string
            {
                return 'Broken';
            }

            public function run(): CheckResult
            {
                throw new RuntimeException('boom');
            }
        });
        app()->tag(['broken.check'], HealthCheck::TAG);

        $results = app(HealthChecker::class)->run();

        expect($results['broken']->status)->toBe(CheckResult::CRITICAL)
            ->and(HealthChecker::overall($results))->toBe(CheckResult::CRITICAL);
    });
});

describe('site health', function () {
    it('registers every check as a Site Health test', function () {
        $tests = apply_filters('site_status_tests', ['direct' => [], 'async' => []]);

        expect($tests['direct'])->toHaveKeys(['alma_database', 'alma_cron', 'alma_failed_jobs', 'alma_mail', 'alma_error_reporting']);

        $result = ($tests['direct']['alma_database']['test'])();

        expect($result)->toMatchArray(['status' => 'good', 'test' => 'alma_database'])
            ->and($result['label'])->toBe('Database connection');
    });

    it('maps warnings to recommended', function () {
        $tests = apply_filters('site_status_tests', ['direct' => []]);

        expect(($tests['direct']['alma_mail']['test'])()['status'])->toBe('recommended');
    });

    it('warns administrators on every admin screen when cron has stalled', function () {
        wp_schedule_event(time() - 7200, 'daily', 'alma_task');
        $this->actingAsWordPressUser(['role' => 'administrator']);

        ob_start();
        app(SiteHealth::class)->cronNotice();

        expect(ob_get_clean())->toContain('Scheduled tasks have stopped');
    });

    it('does not show the cron notice to other users or when cron is fine', function () {
        $this->actingAsWordPressUser(['role' => 'administrator']);
        ob_start();
        app(SiteHealth::class)->cronNotice();
        expect(ob_get_clean())->toBe('');

        wp_schedule_event(time() - 7200, 'daily', 'alma_task');
        wp_set_current_user(WordPress::user(['role' => 'editor'])->ID);
        ob_start();
        app(SiteHealth::class)->cronNotice();
        expect(ob_get_clean())->toBe('');
    });
});

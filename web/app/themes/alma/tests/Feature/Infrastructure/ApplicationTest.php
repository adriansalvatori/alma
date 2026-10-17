<?php

use App\Domains\Commerce\CommerceServiceProvider;
use App\Domains\Lead\LeadServiceProvider;
use App\Infrastructure\Deploy\DeployTask;
use App\Infrastructure\Deploy\Tasks\RunMigrations;
use App\Infrastructure\Queue\CallHandlerJob;
use App\Infrastructure\Queue\Deferred;
use App\Providers\ThemeServiceProvider;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;
use Roots\Acorn\Exceptions\SkipProviderException;

it('boots the application from bootstrap/application.php', function () {
    expect(app()->isBooted())->toBeTrue()
        ->and(config('alma.version'))->toBe('test');
});

it('registers every enabled module and skips one whose prerequisite is missing', function () {
    $loaded = array_keys(app()->getLoadedProviders());

    expect($loaded)->toContain(LeadServiceProvider::class)
        // WooCommerce is not loaded in the suite, so Commerce must not register.
        ->and($loaded)->not->toContain(CommerceServiceProvider::class);
});

it('leaves out a module listed in ALMA_DISABLED_MODULES', function () {
    config(['alma.disabled' => ['lead']]);

    $modules = (new ThemeServiceProvider(app()))->enabledModules();

    expect($modules)->not->toContain(LeadServiceProvider::class);
});

it('runs migrations first and every task in priority order', function () {
    $tasks = collect(app()->tagged(DeployTask::TAG))->sortBy(fn (DeployTask $task) => $task->priority());

    expect($tasks->first())->toBeInstanceOf(RunMigrations::class);
});

it('skips a comma-separated list of tasks', function () {
    $this->artisan('alma:deploy', ['--skip' => ['WarmCaches,FlushRewriteRules']])
        ->doesntExpectOutputToContain('Flush rewrite rules')
        ->doesntExpectOutputToContain('precompile views')
        ->assertSuccessful();
});

it('deploys idempotently', function () {
    $this->artisan('alma:deploy', ['--skip' => ['WarmCaches']])->assertSuccessful();
    $this->artisan('alma:deploy', ['--skip' => ['WarmCaches']])->assertSuccessful();

    expect(Schema::hasTable('jobs'))->toBeTrue()
        ->and(Schema::hasTable('failed_jobs'))->toBeTrue();
});

it('defers work until after the response when no queue is configured', function () {
    Bus::fake();

    Deferred::call(stdClass::class, 'handle');

    Bus::assertDispatchedAfterResponse(CallHandlerJob::class);
});

it('puts deferred work on the queue when one is configured', function () {
    config(['queue.default' => 'database']);
    Bus::fake();

    Deferred::call(stdClass::class, 'handle');

    Bus::assertDispatched(CallHandlerJob::class);
    Bus::assertNotDispatchedAfterResponse(CallHandlerJob::class);
});

it('shares an empty error bag with views WordPress renders', function () {
    expect(view()->shared('errors'))->toBeInstanceOf(ViewErrorBag::class);
});

it('fails loudly in tests when a provider cannot boot', function () {
    $provider = new class(app()) extends ServiceProvider
    {
        public function boot(): void
        {
            throw new RuntimeException('boom');
        }
    };

    expect(fn () => app()->register($provider))->toThrow(SkipProviderException::class);
});

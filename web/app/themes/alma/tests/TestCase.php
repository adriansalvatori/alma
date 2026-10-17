<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Roots\Acorn\Exceptions\SkipProviderException;
use Tests\Support\WordPress\WordPress;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the same application functions.php boots, without booting it inside WordPress.
     */
    public function createApplication(): Application
    {
        $app = (require dirname(__DIR__).'/bootstrap/application.php')->create();

        // Acorn skips a provider that throws while registering or booting and only reports it, so
        // a broken module quietly disappears. In tests that must fail loudly: a missing fake once
        // dropped most of the core hooks without a single failing test.
        $app->afterResolving(ExceptionHandler::class, function ($handler) {
            $handler->reportable(fn (SkipProviderException $e) => throw $e);
        });

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        WordPress::reset();

        parent::setUp();

        // No test reaches the network. A test that needs an HTTP call fakes it with Http::fake().
        Http::preventStrayRequests();
    }

    /**
     * Log a fake WordPress user in for the rest of the test.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function actingAsWordPressUser(array $attributes = []): \WP_User
    {
        $user = WordPress::user($attributes);
        wp_set_current_user($user->ID);

        return $user;
    }
}

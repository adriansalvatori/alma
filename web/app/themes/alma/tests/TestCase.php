<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Tests\Support\WordPress\WordPress;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the same application functions.php boots, without booting it inside WordPress.
     */
    public function createApplication(): Application
    {
        $app = (require dirname(__DIR__).'/bootstrap/application.php')->create();

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

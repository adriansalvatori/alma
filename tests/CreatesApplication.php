<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        // Load Composer autoloader
        require_once dirname(__DIR__) . '/vendor/autoload.php';

        // Bootstrap Acorn
        if (class_exists('\Roots\Acorn\Application')) {
            $app = \Roots\Acorn\Application::getInstance();
            $app->instance('app', $app);
            $app->bind('path.config', fn() => dirname(__DIR__) . '/config');
            $app->bind('path.public', fn() => dirname(__DIR__) . '/web');
        }

        return $app ?? null;
    }
}

<?php

use Roots\Acorn\Application;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
        ->withProviders([
            // Register your service providers
            App\Providers\ThemeServiceProvider::class,
        ])
        ->withMiddleware(function ($middleware) {
            // Configure HTTP middleware for WordPress requests
            $middleware->wordpress([
                Illuminate\Cookie\Middleware\EncryptCookies::class,
                Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                Illuminate\Session\Middleware\StartSession::class,
                Illuminate\View\Middleware\ShareErrorsFromSession::class,
                Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                Illuminate\Routing\Middleware\SubstituteBindings::class,
            ]);

            // You can also configure middleware for web and API routes
            // $middleware->web([...]);
            // $middleware->api([...]);
        })
        ->withExceptions(function ( $exceptions) {
            // Configure exception handling
            // $exceptions->reportable(function (\Throwable $e) {
            //     Log::error($e->getMessage());
            // });
        })
        ->withRouting(
            // Configure routing with named parameters
            web: base_path('routes/web.php'),    // Laravel-style web routes
            api: base_path('routes/api.php'),    // API routes
            wordpress: true,                     // Enable WordPress request handling
        )
        ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });

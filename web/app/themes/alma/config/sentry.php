<?php

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/*
|--------------------------------------------------------------------------
| Sentry
|--------------------------------------------------------------------------
|
| Error reporting to Sentry (SaaS, works from shared hosting). Inactive until
| SENTRY_LARAVEL_DSN is set: there is deliberately no default DSN, so a copied
| project can never report into someone else's Sentry.
|
| The release is the deployed version (APP_VERSION, written by the deploy
| script), so an error links to the exact release that produced it.
|
| App\Infrastructure\Observability\SentryReporting connects this to Acorn's
| exception handler and to fatal errors on pages WordPress renders itself.
|
*/

return [

    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    'release' => env('APP_VERSION'),

    'environment' => env('SENTRY_ENVIRONMENT', defined('WP_ENV') ? WP_ENV : env('WP_ENV', 'production')),

    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),

    // Performance tracing is off unless asked for: it costs quota on every request.
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null ? null : (float) env('SENTRY_TRACES_SAMPLE_RATE'),

    'profiles_sample_rate' => null,

    // No personal data (IP addresses, cookies, request bodies) leaves the site.
    'send_default_pii' => false,

    'ignore_exceptions' => [
        ValidationException::class,
        NotFoundHttpException::class,
    ],

    'ignore_transactions' => [
        '/api/health',
        '/api/health/checks',
    ],

    'breadcrumbs' => [
        'logs' => true,
        'cache' => false,
        'livewire' => true,
        'sql_queries' => false,
        'sql_bindings' => false,
        'queue_info' => true,
        'command_info' => true,
        'http_client_requests' => true,
    ],

    'tracing' => [
        'queue_job_transactions' => false,
        'sql_queries' => false,
        'views' => false,
        'livewire' => false,
        'http_client_requests' => false,
        'default_integrations' => true,
    ],

];

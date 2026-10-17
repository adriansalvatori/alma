<?php

use Monolog\Handler\NullHandler;

/*
|--------------------------------------------------------------------------
| Logging
|--------------------------------------------------------------------------
|
| Acorn merges this file over its own, so its other channels (stderr, syslog,
| slack) still exist; they are simply never selected. The default is a daily
| file: shared hosting has no stderr collector (that was CloudWatch at Remote
| Leverage), and daily files rotate themselves after LOG_DAILY_DAYS.
|
| Logs live in Acorn's storage (web/app/cache/acorn/logs). web/app/.htaccess
| denies that directory, so they are never web-readable.
|
*/

return [

    'default' => env('LOG_CHANNEL', 'daily'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'daily')),
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => (int) env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

    ],

];

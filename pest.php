<?php

/**
 * PHPUnit configuration file
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Test Suite
    |--------------------------------------------------------------------------
    |
    | This array defines the configuration for your test suites.
    |
    */
    'testsuites' => [
        'Unit' => [
            'directory' => __DIR__ . '/tests/Unit',
        ],
        'Feature' => [
            'directory' => __DIR__ . '/tests/Feature',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Coverage
    |--------------------------------------------------------------------------
    |
    | Code coverage configuration
    |
    */
    'coverage' => [
        'enabled' => true,
        'report' => [
            'html' => 'coverage/html',
            'text' => 'coverage/text.txt',
            'clover' => 'coverage/clover.xml',
        ],
        'include' => [
            'web/app/themes/alma/app/**',
        ],
        'exclude' => [
            'web/app/themes/alma/app/Providers/**',
            'web/app/themes/alma/vendor/**',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Parallel Testing
    |--------------------------------------------------------------------------
    |
    | Run tests in parallel for faster execution
    |
    */
    'parallel' => [
        'enabled' => true,
        'processes' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stop on Failure
    |--------------------------------------------------------------------------
    |
    | Stop running tests after first failure
    |
    */
    'stop_on_failure' => false,

    /*
    |--------------------------------------------------------------------------
    | Colors
    |--------------------------------------------------------------------------
    |
    | Enable colored output
    |
    */
    'colors' => true,
];

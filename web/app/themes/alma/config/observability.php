<?php

/*
|--------------------------------------------------------------------------
| Integration call log
|--------------------------------------------------------------------------
|
| Every outbound HTTP call (Laravel's Http client and WordPress' wp_remote_*)
| is recorded to the alma_integration_calls table, so "did the webhook reach
| the CRM?" can be answered from wp-admin (Tools → Integration log) without a
| shell. Secrets are redacted before anything is stored, and recording never
| fails the call it records.
|
*/

return [

    'integration_log' => [

        'enabled' => (bool) env('INTEGRATION_LOG_ENABLED', true),

        // When non-empty, only these hosts are recorded. A leading "*." matches subdomains.
        'hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('INTEGRATION_LOG_HOSTS', ''))))),

        // Never recorded: WordPress' own update checks, the error reporter, and loopback calls.
        // The site's own host (home_url/site_url) is always skipped too, in IntegrationCallRecorder.
        'ignore_hosts' => [
            'api.wordpress.org',
            'downloads.wordpress.org',
            'wordpress.org',
            '*.wordpress.org',
            'sentry.io',
            '*.sentry.io',
            '*.ingest.sentry.io',
            'localhost',
            '127.0.0.1',
        ],

        // Header, query and JSON keys whose values are replaced with [redacted]. Matched as
        // case-insensitive substrings, so "x-api-key" and "access_token" are both caught.
        'redact' => ['authorization', 'cookie', 'token', 'secret', 'password', 'passwd', 'signature', 'api-key', 'api_key', 'apikey', 'key', 'auth', 'session', 'credential'],

        // Request and response bodies are truncated to this many bytes.
        'max_body_bytes' => 65536,

        'retention_days' => (int) env('INTEGRATION_LOG_RETENTION_DAYS', 30),

    ],

];

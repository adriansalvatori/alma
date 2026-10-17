<?php

/*
|--------------------------------------------------------------------------
| Health checks
|--------------------------------------------------------------------------
|
| GET /api/health is a liveness probe for uptime monitors. GET
| /api/health/checks runs the checks below; anonymous callers see only the
| status of each, while the details (timings, counts, ages) need the token
| (X-Health-Token header) or a logged-in administrator.
|
| The same checks appear in Tools → Site Health.
|
*/

return [

    // Leave empty to allow details only for logged-in administrators.
    'token' => env('HEALTH_TOKEN'),

    'cron' => [
        // A due WP-Cron event older than this means the scheduler is behind...
        'behind_after' => (int) env('HEALTH_CRON_BEHIND_AFTER', 900),
        // ...and older than this, that it has stopped. Visitor-triggered WP-Cron stalls on a quiet
        // site, and DISABLE_WP_CRON without a working system cron stops it entirely.
        'stalled_after' => (int) env('HEALTH_CRON_STALLED_AFTER', 3600),
    ],

    'database' => [
        // A select 1 slower than this (milliseconds) is reported as a warning.
        'slow_after_ms' => (int) env('HEALTH_DB_SLOW_AFTER_MS', 500),
    ],

    'failed_jobs' => [
        // Failed deferred calls in the last day before the check warns.
        'warn_at' => (int) env('HEALTH_FAILED_JOBS_WARN_AT', 1),
    ],

];

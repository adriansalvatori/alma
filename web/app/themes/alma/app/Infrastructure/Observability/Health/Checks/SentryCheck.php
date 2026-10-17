<?php

namespace App\Infrastructure\Observability\Health\Checks;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthCheck;

class SentryCheck implements HealthCheck
{
    public function name(): string
    {
        return 'error_reporting';
    }

    public function label(): string
    {
        return __('Error reporting (Sentry)', 'alma');
    }

    public function run(): CheckResult
    {
        return filled(config('sentry.dsn'))
            ? CheckResult::ok('Errors are reported to Sentry.', ['environment' => config('sentry.environment'), 'release' => config('sentry.release')])
            : CheckResult::warning('Errors are only written to the log. Set SENTRY_LARAVEL_DSN to be notified of them.');
    }
}

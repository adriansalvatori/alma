<?php

namespace App\Infrastructure\Observability\Health\Checks;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthCheck;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Deferred calls that failed (a webhook that never reached the CRM, a notification email that
 * did not send) land in failed_jobs when a queue is configured, and in the log otherwise.
 */
class FailedJobsCheck implements HealthCheck
{
    public function name(): string
    {
        return 'failed_jobs';
    }

    public function label(): string
    {
        return __('Failed background jobs', 'alma');
    }

    public function run(): CheckResult
    {
        try {
            $count = DB::table('failed_jobs')->where('failed_at', '>=', now()->subDay())->count();
        } catch (Throwable $e) {
            return CheckResult::warning('The failed_jobs table is missing. Run wp acorn alma:deploy.', ['error' => $e->getMessage()]);
        }

        return $count >= (int) config('health.failed_jobs.warn_at', 1)
            ? CheckResult::warning("{$count} background job(s) failed in the last 24 hours.", ['last_24h' => $count])
            : CheckResult::ok('No background job failed in the last 24 hours.', ['last_24h' => $count]);
    }
}

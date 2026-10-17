<?php

namespace App\Infrastructure\Observability\Health\Checks;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\HealthCheck;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseCheck implements HealthCheck
{
    public function name(): string
    {
        return 'database';
    }

    public function label(): string
    {
        return __('Database connection', 'alma');
    }

    public function run(): CheckResult
    {
        $start = hrtime(true);

        try {
            DB::select('select 1');
        } catch (Throwable $e) {
            return CheckResult::critical('The database did not answer.', ['error' => $e->getMessage()]);
        }

        $ms = (int) round((hrtime(true) - $start) / 1e6);
        $details = ['latency_ms' => $ms];

        return $ms >= (int) config('health.database.slow_after_ms', 500)
            ? CheckResult::warning("The database answered slowly ({$ms} ms).", $details)
            : CheckResult::ok("The database answered in {$ms} ms.", $details);
    }
}

<?php

namespace App\Infrastructure\Observability\Health\Checks;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\CronHealth;
use App\Infrastructure\Observability\Health\HealthCheck;

class CronCheck implements HealthCheck
{
    public function __construct(private readonly CronHealth $cron) {}

    public function name(): string
    {
        return 'cron';
    }

    public function label(): string
    {
        return __('Scheduled tasks (WP-Cron)', 'alma');
    }

    public function run(): CheckResult
    {
        $state = $this->cron->assess();
        $minutes = (int) floor($state['overdue_seconds'] / 60);

        return match ($state['status']) {
            CronHealth::STALLED => CheckResult::critical(
                $state['wp_cron_disabled']
                    ? "Scheduled tasks have not run for {$minutes} minutes. DISABLE_WP_CRON is set, so the system cron is not calling WordPress."
                    : "Scheduled tasks have not run for {$minutes} minutes. Set up a real cron (see docs/operations.md).",
                $state,
            ),
            CronHealth::BEHIND => CheckResult::warning("Scheduled tasks are {$minutes} minutes behind.", $state),
            default => CheckResult::ok('Scheduled tasks are running on time.', $state),
        };
    }
}

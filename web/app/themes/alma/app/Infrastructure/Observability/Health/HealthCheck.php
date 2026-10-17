<?php

namespace App\Infrastructure\Observability\Health;

/**
 * One health check, shown at /api/health/checks and in Tools → Site Health. Add a check by
 * implementing this and listing it in HealthChecker::CHECKS (or tagging it alma.health-checks).
 */
interface HealthCheck
{
    public const TAG = 'alma.health-checks';

    /**
     * A stable machine name: database, cron, ...
     */
    public function name(): string;

    /**
     * Shown in Site Health.
     */
    public function label(): string;

    /**
     * Must not throw: a check that cannot run reports critical with the reason.
     */
    public function run(): CheckResult;
}

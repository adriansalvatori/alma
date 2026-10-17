<?php

namespace App\Infrastructure\Observability\Health;

use App\Infrastructure\Observability\Health\Checks\CronCheck;
use App\Infrastructure\Observability\Health\Checks\DatabaseCheck;
use App\Infrastructure\Observability\Health\Checks\FailedJobsCheck;
use App\Infrastructure\Observability\Health\Checks\MailCheck;
use App\Infrastructure\Observability\Health\Checks\SentryCheck;
use Illuminate\Contracts\Container\Container;
use Throwable;

/**
 * Runs every health check. Each check fails on its own: one that throws is reported as critical
 * instead of taking the others (or the health endpoint) down with it.
 */
class HealthChecker
{
    /** @var list<class-string<HealthCheck>> */
    public const CHECKS = [
        DatabaseCheck::class,
        CronCheck::class,
        FailedJobsCheck::class,
        MailCheck::class,
        SentryCheck::class,
    ];

    public function __construct(private readonly Container $container) {}

    /**
     * @return list<HealthCheck>
     */
    public function checks(): array
    {
        $checks = array_map(fn (string $class) => $this->container->make($class), self::CHECKS);

        foreach ($this->container->tagged(HealthCheck::TAG) as $check) {
            $checks[] = $check;
        }

        return $checks;
    }

    /**
     * @return array<string, CheckResult>
     */
    public function run(): array
    {
        $results = [];

        foreach ($this->checks() as $check) {
            try {
                $results[$check->name()] = $check->run();
            } catch (Throwable $e) {
                $results[$check->name()] = CheckResult::critical('The check itself failed.', ['error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    /**
     * @param  array<string, CheckResult>  $results
     */
    public static function overall(array $results): string
    {
        $statuses = array_map(fn (CheckResult $result) => $result->status, $results);

        return match (true) {
            in_array(CheckResult::CRITICAL, $statuses, true) => CheckResult::CRITICAL,
            in_array(CheckResult::WARNING, $statuses, true) => CheckResult::WARNING,
            default => CheckResult::OK,
        };
    }
}

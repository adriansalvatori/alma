<?php

namespace App\Infrastructure\Observability;

use App\Infrastructure\Observability\Health\CheckResult;
use App\Infrastructure\Observability\Health\CronHealth;
use App\Infrastructure\Observability\Health\HealthCheck;
use App\Infrastructure\Observability\Health\HealthChecker;

/**
 * The health checks in Tools → Site Health, where a site owner already looks, plus a dashboard
 * notice when WP-Cron has stalled: the failure shared hosting produces most often and that no one
 * notices until scheduled work is days behind.
 */
class SiteHealth
{
    public function __construct(
        private readonly HealthChecker $checker,
        private readonly CronHealth $cron,
    ) {}

    public function register(): void
    {
        add_filter('site_status_tests', [$this, 'tests']);
        add_action('admin_notices', [$this, 'cronNotice']);
    }

    /**
     * @param  array<string, array<string, mixed>>  $tests
     * @return array<string, array<string, mixed>>
     */
    public function tests(array $tests): array
    {
        foreach ($this->checker->checks() as $check) {
            $tests['direct']['alma_'.$check->name()] = [
                'label' => $check->label(),
                'test' => fn () => $this->result($check),
            ];
        }

        return $tests;
    }

    /**
     * @return array<string, mixed>
     */
    public function result(HealthCheck $check): array
    {
        $result = $check->run();

        return [
            'label' => $check->label(),
            'status' => match ($result->status) {
                CheckResult::CRITICAL => 'critical',
                CheckResult::WARNING => 'recommended',
                default => 'good',
            },
            'badge' => ['label' => 'Alma', 'color' => $result->status === CheckResult::OK ? 'green' : 'orange'],
            'description' => '<p>'.esc_html($result->summary).'</p>',
            'actions' => '',
            'test' => 'alma_'.$check->name(),
        ];
    }

    public function cronNotice(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $state = $this->cron->assess();

        if ($state['status'] !== CronHealth::STALLED) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
            esc_html__('Scheduled tasks have stopped.', 'alma'),
            esc_html(sprintf(
                /* translators: %d: minutes */
                __('The oldest due task has waited %d minutes. Set up a system cron for WP-Cron (see docs/operations.md).', 'alma'),
                (int) floor($state['overdue_seconds'] / 60),
            )),
        );
    }
}

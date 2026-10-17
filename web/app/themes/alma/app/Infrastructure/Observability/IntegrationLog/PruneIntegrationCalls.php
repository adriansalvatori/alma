<?php

namespace App\Infrastructure\Observability\IntegrationLog;

use Throwable;

/**
 * Daily WP-Cron job that deletes integration calls past the retention window, in batches small
 * enough to finish inside a shared host's request time limit.
 */
class PruneIntegrationCalls
{
    public const HOOK = 'alma_prune_integration_calls';

    private const BATCH = 5000;

    public function register(): void
    {
        add_action(self::HOOK, function (): void {
            $this->prune();
        });
        add_action('init', [$this, 'schedule']);
    }

    public function schedule(): void
    {
        if (! wp_next_scheduled(self::HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
        }
    }

    /**
     * @return int Rows deleted.
     */
    public function prune(): int
    {
        $cutoff = now()->subDays(max(1, (int) config('observability.integration_log.retention_days', 30)));
        $deleted = 0;

        try {
            for ($pass = 0; $pass < 20; $pass++) {
                $ids = IntegrationCall::query()->where('created_at', '<', $cutoff)->orderBy('id')->limit(self::BATCH)->pluck('id');

                if ($ids->isEmpty()) {
                    break;
                }

                $deleted += IntegrationCall::query()->whereIn('id', $ids)->delete();
            }
        } catch (Throwable) {
            // Missing table before the first deploy: nothing to prune.
        }

        return $deleted;
    }
}

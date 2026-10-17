<?php

namespace App\Infrastructure\Observability\Health;

/**
 * Is WP-Cron actually running?
 *
 * WP-Cron runs when a visitor loads a page, so on a quiet site, or with DISABLE_WP_CRON set and no
 * system cron behind it, scheduled work (lead retention purges, log pruning, plugin tasks) quietly
 * stops. A stopped scheduler still lists its events, so "events are scheduled" proves nothing; the
 * signal is how long the oldest *due* event has been waiting.
 */
class CronHealth
{
    public const OK = 'ok';

    public const BEHIND = 'behind';

    public const STALLED = 'stalled';

    /**
     * @param  array<int|string, mixed>|null  $cron  The _get_cron_array() structure, for tests.
     * @return array{status: string, overdue_seconds: int, overdue_events: int, wp_cron_disabled: bool}
     */
    public function assess(?array $cron = null, ?int $now = null): array
    {
        $now ??= time();
        $cron ??= function_exists('_get_cron_array') ? (array) _get_cron_array() : [];

        $oldest = null;
        $overdue = 0;

        foreach ($cron as $timestamp => $hooks) {
            if (! is_numeric($timestamp) || (int) $timestamp > $now) {
                continue;
            }

            $overdue += is_array($hooks) ? array_sum(array_map(fn ($events) => is_array($events) ? count($events) : 1, $hooks)) : 1;
            $oldest = $oldest === null ? (int) $timestamp : min($oldest, (int) $timestamp);
        }

        $age = $oldest === null ? 0 : $now - $oldest;

        $status = match (true) {
            $age >= (int) config('health.cron.stalled_after', 3600) => self::STALLED,
            $age >= (int) config('health.cron.behind_after', 900) => self::BEHIND,
            default => self::OK,
        };

        return [
            'status' => $status,
            'overdue_seconds' => $age,
            'overdue_events' => $overdue,
            'wp_cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
        ];
    }
}

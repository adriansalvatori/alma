<?php

namespace App\Domains\Lead\Hooks;

use App\Domains\Lead\Services\LeadPurger;
use Illuminate\Support\Facades\Log;

/**
 * Deletes expired leads once a day through WP-Cron. On shared hosting, point a system cron at
 * wp-cron.php (docs/deployment.md) so this runs even on a quiet site.
 */
class LeadRetention
{
    public const HOOK = 'alma_leads_purge';

    public function __construct(private LeadPurger $purger) {}

    public function register(): void
    {
        add_action('init', [$this, 'schedule']);
        add_action(self::HOOK, [$this, 'run']);
    }

    public function schedule(): void
    {
        if (! wp_next_scheduled(self::HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
        }
    }

    public function run(): void
    {
        $deleted = $this->purger->purge();

        if ($deleted > 0) {
            Log::info("Lead retention: deleted {$deleted} expired lead(s).");
        }
    }
}

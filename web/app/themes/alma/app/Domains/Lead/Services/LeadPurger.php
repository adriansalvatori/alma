<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use Illuminate\Support\Carbon;

/**
 * Deletes leads (and their audit rows) older than the retention window.
 *
 * Works in batches so a large first purge on shared hosting stays well inside the PHP time limit;
 * the daily cron simply finishes the backlog over the following runs if it has to stop.
 */
class LeadPurger
{
    private const BATCH = 500;

    public function __construct(private LeadSettings $settings) {}

    public function cutoff(): Carbon
    {
        return Carbon::now()->subDays($this->settings->retentionDays());
    }

    /**
     * @return int Leads deleted (or that would be, with $dryRun).
     */
    public function purge(bool $dryRun = false, int $maxBatches = 20): int
    {
        $query = Lead::query()->where('created_at', '<', $this->cutoff());

        if ($dryRun) {
            return $query->count();
        }

        $deleted = 0;

        for ($batch = 0; $batch < $maxBatches; $batch++) {
            $ids = (clone $query)->orderBy('id')->limit(self::BATCH)->pluck('id')->all();

            if ($ids === []) {
                break;
            }

            LeadActivity::query()->whereIn('lead_id', $ids)->delete();
            $deleted += Lead::query()->whereIn('id', $ids)->delete();
        }

        return $deleted;
    }
}

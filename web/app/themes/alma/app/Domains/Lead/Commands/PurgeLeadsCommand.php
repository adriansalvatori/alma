<?php

namespace App\Domains\Lead\Commands;

use App\Domains\Lead\Services\LeadPurger;
use App\Domains\Lead\Services\LeadSettings;
use Illuminate\Console\Command;

class PurgeLeadsCommand extends Command
{
    protected $signature = 'alma:leads:purge {--dry-run : Count what would be deleted without deleting it}';

    protected $description = 'Delete leads older than the retention window (Leads → Settings, at least 30 days).';

    public function handle(LeadPurger $purger, LeadSettings $settings): int
    {
        $days = $settings->retentionDays();
        $cutoff = $purger->cutoff()->toDateTimeString();

        if ($this->option('dry-run')) {
            $this->info("{$purger->purge(dryRun: true)} lead(s) older than {$days} days (before {$cutoff}) would be deleted.");

            return self::SUCCESS;
        }

        $this->info("Deleted {$purger->purge(maxBatches: PHP_INT_MAX)} lead(s) older than {$days} days (before {$cutoff}).");

        return self::SUCCESS;
    }
}

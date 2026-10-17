<?php

namespace App\Domains\Seo\Commands;

use App\Domains\Seo\Settings\TsfConfigurator;
use Illuminate\Console\Command;

class ApplySeoSettingsCommand extends Command
{
    protected $signature = 'alma:seo:apply {--dry-run : Show what would change without writing}';

    protected $description = 'Apply config/seo.php to The SEO Framework settings.';

    public function handle(TsfConfigurator $configurator): int
    {
        if (! $configurator->available()) {
            $this->warn('The SEO Framework is not active. Nothing to do.');

            return self::SUCCESS;
        }

        $plan = $this->option('dry-run') ? $configurator->plan() : $configurator->apply();

        foreach ($plan['changes'] as $key => $change) {
            $this->components->twoColumnDetail($key, $this->export($change['from']).' → '.$this->export($change['to']));
        }

        if ($plan['changes'] === []) {
            $this->info('TSF settings already match config/seo.php.');
        } else {
            $this->info(($this->option('dry-run') ? 'Would change ' : 'Changed ').count($plan['changes']).' setting(s).');
        }

        if ($plan['unknown'] !== []) {
            $this->error('Unknown keys (not written): '.implode(', ', $plan['unknown']));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function export(mixed $value): string
    {
        return is_scalar($value) || $value === null ? var_export($value, true) : (string) json_encode($value);
    }
}

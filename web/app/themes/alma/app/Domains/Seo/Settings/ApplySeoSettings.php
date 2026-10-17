<?php

namespace App\Domains\Seo\Settings;

use App\Infrastructure\Deploy\DeployTask;
use RuntimeException;

/**
 * Re-applies config/seo.php to The SEO Framework on every deploy. Never fatal: a site with stale
 * SEO settings is still a working site.
 */
class ApplySeoSettings implements DeployTask
{
    public function __construct(private readonly TsfConfigurator $configurator) {}

    public function label(): string
    {
        return 'Apply SEO settings (The SEO Framework)';
    }

    public function priority(): int
    {
        return 50;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        if (! config('seo.tsf.apply_on_deploy', true) || ! $this->configurator->available()) {
            return;
        }

        $plan = $this->configurator->apply();

        if ($plan['unknown'] !== []) {
            throw new RuntimeException('Unknown TSF keys in config/seo.php were not written: '.implode(', ', $plan['unknown']));
        }
    }
}

<?php

namespace App\Infrastructure\Deploy\Tasks;

use App\Infrastructure\Deploy\DeployTask;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

/**
 * Clear every compiled cache from the previous release, then precompile the Blade views so the
 * first visitor after a deploy does not pay for it.
 *
 * Config and routes are deliberately not cached: Bedrock loads .env on every request anyway, and
 * a cached config would silently keep serving the old values after an .env edit on the server.
 */
class WarmCaches implements DeployTask
{
    public function label(): string
    {
        return 'Clear caches and precompile views';
    }

    public function priority(): int
    {
        return 100;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        foreach (['optimize:clear', 'view:cache'] as $command) {
            if (Artisan::call($command) !== 0) {
                throw new RuntimeException("{$command} failed: ".trim(Artisan::output()));
            }
        }
    }
}

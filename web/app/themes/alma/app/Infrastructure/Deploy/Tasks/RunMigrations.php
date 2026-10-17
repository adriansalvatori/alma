<?php

namespace App\Infrastructure\Deploy\Tasks;

use App\Infrastructure\Deploy\DeployTask;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class RunMigrations implements DeployTask
{
    public function label(): string
    {
        return 'Run database migrations';
    }

    public function priority(): int
    {
        return 0;
    }

    public function fatal(): bool
    {
        return true;
    }

    public function run(): void
    {
        if (Artisan::call('migrate', ['--force' => true]) !== 0) {
            throw new RuntimeException(trim(Artisan::output()) ?: 'migrate exited with an error.');
        }
    }
}

<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Console\Commands\DeployCommand;
use App\Infrastructure\Deploy\Tasks\FlushRewriteRules;
use App\Infrastructure\Deploy\Tasks\RunMigrations;
use App\Infrastructure\Deploy\Tasks\WarmCaches;

/**
 * Site-wide infrastructure that every module relies on. Always registered, never disabled.
 */
class CoreServiceProvider extends ModuleServiceProvider
{
    protected array $commands = [
        DeployCommand::class,
    ];

    protected array $deployTasks = [
        RunMigrations::class,
        FlushRewriteRules::class,
        WarmCaches::class,
    ];

    protected ?string $migrations = __DIR__.'/../Database/Migrations';
}

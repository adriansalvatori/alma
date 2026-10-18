<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Console\Commands\DeployCommand;
use App\Infrastructure\Deploy\Tasks\FlushRewriteRules;
use App\Infrastructure\Deploy\Tasks\RunMigrations;
use App\Infrastructure\Deploy\Tasks\WarmCaches;
use App\Infrastructure\Mail\SmtpMailer;
use App\Infrastructure\Observability\IntegrationLog\IntegrationCallRecorder;
use App\Infrastructure\Observability\IntegrationLog\IntegrationLogScreen;
use App\Infrastructure\Observability\IntegrationLog\PruneIntegrationCalls;
use App\Infrastructure\Observability\SentryReporting;
use App\Infrastructure\Observability\SiteHealth;
use App\Infrastructure\Security\HeadCleanup;
use App\Infrastructure\Security\UserEnumerationGuard;
use App\Infrastructure\Security\XmlRpcLockdown;
use App\Infrastructure\WordPress\DisableComments;

/**
 * Site-wide infrastructure that every module relies on. Always registered, never disabled.
 */
class CoreServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        SentryReporting::class,
        XmlRpcLockdown::class,
        HeadCleanup::class,
        UserEnumerationGuard::class,
        SmtpMailer::class,
        IntegrationCallRecorder::class,
        PruneIntegrationCalls::class,
        IntegrationLogScreen::class,
        SiteHealth::class,
        DisableComments::class,
    ];

    protected array $commands = [
        DeployCommand::class,
    ];

    protected array $deployTasks = [
        RunMigrations::class,
        FlushRewriteRules::class,
        WarmCaches::class,
    ];

    protected ?string $migrations = __DIR__.'/../Database/Migrations';

    protected ?string $apiRoutes = __DIR__.'/../routes/api.php';
}

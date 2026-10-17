<?php

namespace App\Domains\Lead;

use App\Domains\Lead\Admin\LeadsAdmin;
use App\Domains\Lead\Commands\PurgeLeadsCommand;
use App\Domains\Lead\Deploy\GrantLeadCapability;
use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Hooks\AttributionCookie;
use App\Domains\Lead\Hooks\LeadPrivacy;
use App\Domains\Lead\Hooks\LeadRetention;
use App\Domains\Lead\Listeners\RecordLeadDispatch;
use App\Domains\Lead\Listeners\SendLeadNotification;
use App\Domains\Lead\Listeners\SendLeadWebhook;
use App\Domains\Lead\Services\LeadSettings;
use App\Infrastructure\Providers\ModuleServiceProvider;

/**
 * Leads: capture (the lead form), attribution, spam protection, notification email, CRM webhook,
 * the wp-admin screens, retention and privacy. See docs/lead.md.
 */
class LeadServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        AttributionCookie::class,
        LeadRetention::class,
        LeadPrivacy::class,
        LeadsAdmin::class,
    ];

    protected array $listen = [
        LeadCaptured::class => [RecordLeadDispatch::class],
    ];

    protected array $listenAfterResponse = [
        LeadCaptured::class => [SendLeadNotification::class, SendLeadWebhook::class],
    ];

    protected array $commands = [
        PurgeLeadsCommand::class,
    ];

    protected array $deployTasks = [
        GrantLeadCapability::class,
    ];

    protected ?string $migrations = __DIR__.'/Database/Migrations';

    public function register(): void
    {
        parent::register();

        $this->app->singleton(LeadSettings::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The wp-admin screens live with the module: view('lead::admin.index').
        $this->loadViewsFrom(__DIR__.'/resources/views', 'lead');
    }
}

<?php

namespace App\Domains\Ai;

use App\Domains\Ai\Admin\AiAccessScreen;
use App\Domains\Ai\Admin\AuthorizationProbe;
use App\Domains\Ai\Commands\AgentCommand;
use App\Domains\Ai\Deploy\ActivateMcpAdapter;
use App\Domains\Ai\Deploy\ReconcileAgent;
use App\Domains\Ai\Hooks\RegisterAbilities;
use App\Domains\Ai\Hooks\ToolAuditLog;
use App\Infrastructure\Providers\ModuleServiceProvider;

/**
 * AI access: the site's abilities (WordPress Abilities API) served to Claude over MCP by the
 * mcp-adapter plugin, the agent user and its credentials, and an audit log of every call.
 * See docs/ai.md.
 */
class AiServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [
        RegisterAbilities::class,
        ToolAuditLog::class,
        AuthorizationProbe::class,
        AiAccessScreen::class,
    ];

    protected array $commands = [
        AgentCommand::class,
    ];

    protected array $deployTasks = [
        ActivateMcpAdapter::class,
        ReconcileAgent::class,
    ];

    protected ?string $migrations = __DIR__.'/Database/Migrations';

    /**
     * The Abilities API arrived in WordPress 6.9; without it there is nothing to expose.
     */
    public static function enabled(): bool
    {
        return function_exists('wp_register_ability') && (bool) config('ai.enabled', true);
    }

    public function register(): void
    {
        parent::register();

        // One instance: it keeps the start times of calls in flight between its hooks.
        $this->app->singleton(ToolAuditLog::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/resources/views', 'ai');
    }
}

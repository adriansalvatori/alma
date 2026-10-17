<?php

namespace App\Domains\Ai\Deploy;

use App\Domains\Ai\Provisioning\AgentProvisioner;
use App\Infrastructure\Deploy\DeployTask;

/**
 * Re-apply the agent's capabilities from config on every release, so a flag turned off in .env
 * revokes access on the next deploy. Never issues a credential: deploy output is logged.
 */
class ReconcileAgent implements DeployTask
{
    public function __construct(private readonly AgentProvisioner $agent) {}

    public function label(): string
    {
        return 'Reconcile the AI agent user';
    }

    public function priority(): int
    {
        return 60;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        $this->agent->ensure();
    }
}

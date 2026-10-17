<?php

namespace App\Domains\Ai\Commands;

use App\Domains\Ai\Provisioning\AgentProvisioner;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Reconcile the AI agent user, and issue or revoke its credentials from the command line.
 * Settings → AI Access does the same in wp-admin, for people without a shell.
 */
class AgentCommand extends Command
{
    protected $signature = 'alma:ai:agent
        {--issue= : Mint a named Application Password (shown once), e.g. --issue="Ada, Claude Desktop"}
        {--revoke= : Revoke an Application Password by its uuid}
        {--list : List the agent\'s Application Passwords}';

    protected $description = 'Create or reconcile the AI agent user from config/ai.php; issue, revoke or list its credentials.';

    public function handle(AgentProvisioner $agent): int
    {
        try {
            $state = $agent->ensure();
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->twoColumnDetail(
            "User {$agent->login()} (#{$state['user_id']})",
            $state['created'] ? '<fg=green>created</>' : 'up to date',
        );
        $this->components->twoColumnDetail('Granted', implode(', ', $state['granted']) ?: '—');
        $this->components->twoColumnDetail('Denied', implode(', ', $state['denied']) ?: '—');

        if (is_string($uuid = $this->option('revoke')) && $uuid !== '') {
            if (! $agent->revoke($uuid)) {
                $this->error("No credential {$uuid} on the agent user.");

                return self::FAILURE;
            }

            $this->info("Revoked {$uuid}.");
        }

        if (is_string($label = $this->option('issue')) && $label !== '') {
            try {
                $issued = $agent->issue($label);
            } catch (RuntimeException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            $this->newLine();
            $this->warn('Copy this now. It is not stored and will not be shown again:');
            $this->line("  Username: {$agent->login()}");
            $this->line("  Password: {$issued['password']}");
            $this->line("  Name:     {$issued['name']} ({$issued['uuid']})");
        }

        if ($this->option('list')) {
            $this->table(
                ['uuid', 'name', 'created', 'last used'],
                array_map(fn (array $credential) => [
                    $credential['uuid'],
                    $credential['name'],
                    $credential['created'] ? gmdate('Y-m-d H:i', $credential['created']) : '—',
                    $credential['last_used'] ? gmdate('Y-m-d H:i', $credential['last_used']) : 'never',
                ], $agent->credentials()),
            );
        }

        return self::SUCCESS;
    }
}

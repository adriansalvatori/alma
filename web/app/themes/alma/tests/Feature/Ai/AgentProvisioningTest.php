<?php

use App\Domains\Ai\Deploy\ReconcileAgent;
use App\Domains\Ai\Provisioning\AgentProvisioner;
use App\Infrastructure\Deploy\DeployTask;
use Tests\Support\WordPress\WordPress;

function ai_agent_flags(array $flags): void
{
    foreach ($flags as $group => $enabled) {
        config(["ai.agent.grants.{$group}.enabled" => $enabled]);
    }
}

it('creates the agent as an editor with the configured grants', function () {
    ai_agent_flags(['publish' => false, 'upload_media' => true, 'read_leads' => false]);

    $state = app(AgentProvisioner::class)->ensure();
    $agent = get_userdata($state['user_id']);

    expect($state['created'])->toBeTrue()
        ->and($agent->user_login)->toBe('ai-agent')
        ->and($agent->roles)->toBe(['editor'])
        ->and(user_can($agent, 'upload_files'))->toBeTrue()
        ->and(user_can($agent, 'alma_manage_leads'))->toBeFalse();
});

it('takes back what the editor role would otherwise grant', function () {
    ai_agent_flags(['publish' => false, 'edit_published' => false]);

    $agent = get_userdata(app(AgentProvisioner::class)->ensure()['user_id']);

    // Editors can publish by role; the explicit deny is what stops the agent.
    expect(user_can($agent, 'publish_pages'))->toBeFalse()
        ->and(user_can($agent, 'edit_published_pages'))->toBeFalse()
        ->and(user_can($agent, 'edit_pages'))->toBeTrue();
});

it('tightens as well as widens on the next run', function () {
    ai_agent_flags(['read_leads' => true]);
    $id = app(AgentProvisioner::class)->ensure()['user_id'];
    expect(user_can($id, 'alma_manage_leads'))->toBeTrue();

    ai_agent_flags(['read_leads' => false]);
    $again = app(AgentProvisioner::class)->ensure();

    expect($again['created'])->toBeFalse()
        ->and($again['user_id'])->toBe($id)
        ->and(user_can($id, 'alma_manage_leads'))->toBeFalse();
});

it('puts a promoted agent back to editor', function () {
    $id = app(AgentProvisioner::class)->ensure()['user_id'];
    get_userdata($id)->set_role('administrator');

    app(AgentProvisioner::class)->ensure();

    expect(get_userdata($id)->roles)->toBe(['editor']);
});

it('issues named credentials, lists them and revokes one', function () {
    $agent = app(AgentProvisioner::class);
    $agent->ensure();

    $ada = $agent->issue('Ada, Claude Desktop');
    $agent->issue('Grace, Claude Code');

    expect($ada['password'])->not->toBe('')
        ->and(array_column($agent->credentials(), 'name'))->toBe(['Ada, Claude Desktop', 'Grace, Claude Code'])
        ->and($agent->revoke($ada['uuid']))->toBeTrue()
        ->and(array_column($agent->credentials(), 'name'))->toBe(['Grace, Claude Code'])
        ->and($agent->revoke('missing'))->toBeFalse();
});

it('refuses an unnamed credential', function () {
    $agent = app(AgentProvisioner::class);
    $agent->ensure();

    expect(fn () => $agent->issue('  '))->toThrow(RuntimeException::class);
});

it('reconciles on every deploy but never mints a credential', function () {
    $task = collect(app()->tagged(DeployTask::TAG))->first(fn (DeployTask $task) => $task instanceof ReconcileAgent);

    expect($task)->not->toBeNull()
        ->and($task->fatal())->toBeFalse();

    $task->run();
    $task->run();

    $agent = app(AgentProvisioner::class)->user();

    expect($agent)->not->toBeNull()
        ->and(WordPress::$options['__app_passwords'] ?? [])->toBe([]);
});

it('prints a new credential once from the command', function () {
    $this->artisan('alma:ai:agent', ['--issue' => 'Ada, Claude Desktop'])
        ->expectsOutputToContain('will not be shown again')
        ->expectsOutputToContain('Username: ai-agent')
        ->assertSuccessful();

    expect(app(AgentProvisioner::class)->credentials())->toHaveCount(1);
});

it('reconciles without issuing anything when run bare', function () {
    $this->artisan('alma:ai:agent')->assertSuccessful();

    expect(app(AgentProvisioner::class)->user())->not->toBeNull()
        ->and(app(AgentProvisioner::class)->credentials())->toBe([]);
});

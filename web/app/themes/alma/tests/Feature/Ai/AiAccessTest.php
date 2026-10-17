<?php

use App\Domains\Ai\Admin\AiAccessScreen;
use App\Domains\Ai\Admin\AuthorizationProbe;
use App\Domains\Ai\Deploy\ActivateMcpAdapter;
use App\Domains\Ai\Provisioning\AgentProvisioner;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $this->actingAsWordPressUser(['role' => 'administrator']);
});

it('shows a freshly issued credential once', function () {
    app(AgentProvisioner::class)->ensure();
    set_transient('alma_ai_issued_'.get_current_user_id(), ['password' => 'secret-once', 'name' => 'Ada', 'uuid' => 'u1', 'login' => 'ai-agent'], 300);

    $first = app(AiAccessScreen::class)->data();
    $second = app(AiAccessScreen::class)->data();

    expect($first['issued']['password'])->toBe('secret-once')
        ->and($first['desktopConfig'])->toContain('secret-once')
        ->and($second['issued'])->toBeNull()
        ->and($second['desktopConfig'])->not->toContain('secret-once');
});

it('builds a ready-to-paste client config for the MCP endpoint', function () {
    $config = json_decode(app(AiAccessScreen::class)->clientConfig('https://example.test/wp-json/mcp/mcp-adapter-default-server'), true);
    $server = array_values($config['mcpServers'])[0];

    expect($server['command'])->toBe('npx')
        ->and($server['args'])->toBe(['-y', '@automattic/mcp-wordpress-remote@latest'])
        ->and($server['env'])->toBe([
            'WP_API_URL' => 'https://example.test/wp-json/mcp/mcp-adapter-default-server',
            'WP_API_USERNAME' => 'ai-agent',
            'WP_API_PASSWORD' => '<application password>',
        ]);
});

it('reports the capability groups as configured and as they are on the user', function () {
    config(['ai.agent.grants.read_leads.enabled' => true]);
    app(AgentProvisioner::class)->ensure();

    $grants = app(AiAccessScreen::class)->data()['grants'];

    expect($grants['read_leads'])->toMatchArray(['enabled' => true, 'actual' => true])
        ->and($grants['publish'])->toMatchArray(['enabled' => false, 'actual' => false]);
});

it('lists every prerequisite in the status panel', function () {
    WordPress::$options['__app_passwords_available'] = false;

    $status = collect(app(AiAccessScreen::class)->status())->keyBy('label');

    expect($status->keys()->all())->toBe(['HTTPS', 'Application Passwords', 'MCP adapter plugin', 'Authorization header'])
        ->and($status['Application Passwords']['ok'])->toBeFalse()
        ->and($status['Authorization header']['ok'])->toBeNull();
});

it('detects the Authorization header in either server variable', function () {
    expect(AuthorizationProbe::headerReceived(['HTTP_AUTHORIZATION' => 'Bearer x']))->toBeTrue()
        ->and(AuthorizationProbe::headerReceived(['REDIRECT_HTTP_AUTHORIZATION' => 'Bearer x']))->toBeTrue()
        ->and(AuthorizationProbe::headerReceived([]))->toBeFalse();
});

it('records whether the host strips the header', function (string $body, string $expected) {
    WordPress::respondWith(200, $body);

    $result = app(AuthorizationProbe::class)->run();

    expect($result['status'])->toBe($expected)
        ->and(app(AuthorizationProbe::class)->last()['status'])->toBe($expected)
        ->and(WordPress::$httpRequests[0]['args']['headers'])->toHaveKey('Authorization');
})->with([
    'passes' => ['{"received":true}', 'ok'],
    'stripped' => ['{"received":false}', 'stripped'],
    'odd answer' => ['<html>', 'unknown'],
]);

it('registers the probe endpoint', function () {
    do_action('rest_api_init');

    expect(WordPress::$options['__rest_routes'])->toHaveKey('alma/v1/ai/authorization-probe');
});

function ai_fake_adapter(bool $bundledVendor): string
{
    $file = WP_PLUGIN_DIR.'/'.ActivateMcpAdapter::PLUGIN;
    is_dir(dirname($file).'/vendor') || mkdir(dirname($file).'/vendor', 0755, true);
    file_put_contents($file, '<?php');
    $vendor = dirname($file).'/vendor/autoload_packages.php';
    $bundledVendor ? file_put_contents($vendor, '<?php') : (is_file($vendor) && unlink($vendor));

    return $file;
}

it('activates the MCP adapter when it is installed, inactive and able to load', function () {
    ai_fake_adapter(bundledVendor: true);
    update_option('active_plugins', ['autodescription/autodescription.php']);

    (new ActivateMcpAdapter)->run();
    (new ActivateMcpAdapter)->run();

    expect(get_option('active_plugins'))->toBe(['autodescription/autodescription.php', ActivateMcpAdapter::PLUGIN]);
});

it('refuses to activate an adapter that cannot load, instead of breaking the site', function () {
    // No bundled vendor/ and no WP_MCP_AUTOLOAD: activating it would 500 every page.
    ai_fake_adapter(bundledVendor: false);
    update_option('active_plugins', []);

    expect(fn () => (new ActivateMcpAdapter)->run())->toThrow(RuntimeException::class, 'WP_MCP_AUTOLOAD')
        ->and(get_option('active_plugins'))->toBe([]);
});

it('leaves a missing MCP adapter alone', function () {
    is_file($file = WP_PLUGIN_DIR.'/'.ActivateMcpAdapter::PLUGIN) && unlink($file);
    update_option('active_plugins', []);

    (new ActivateMcpAdapter)->run();

    expect(get_option('active_plugins'))->toBe([]);
});

it('renders the screen', function () {
    app(AgentProvisioner::class)->ensure();
    app(AgentProvisioner::class)->issue('Ada, Claude Desktop');

    $html = view('ai::admin.access', app(AiAccessScreen::class)->data())->render();

    expect($html)->toContain('AI Access')
        ->toContain('Ada, Claude Desktop')
        ->toContain('mcp-adapter-default-server')
        ->toContain('name="action" value="alma_ai_issue"')
        ->toContain('name="action" value="alma_ai_revoke"');
});

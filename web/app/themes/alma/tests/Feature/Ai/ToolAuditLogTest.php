<?php

use App\Domains\Ai\Hooks\ToolAuditLog;
use App\Domains\Ai\Models\ToolCall;
use App\Domains\Seo\Redirects\RedirectManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['ai.audit.prune_odds' => 1_000_000]);
    $this->user = $this->actingAsWordPressUser(['role' => 'editor']);
});

it('records a successful call with who made it and how long it took', function () {
    ai_test_run_ability('alma/list-patterns', ['all' => true]);

    $call = ToolCall::query()->sole();

    expect($call->ability)->toBe('alma/list-patterns')
        ->and($call->outcome)->toBe(ToolCall::SUCCEEDED)
        ->and($call->user_id)->toBe($this->user->ID)
        ->and($call->duration_ms)->toBeInt()
        ->and($call->input_summary)->toBe('{"all":true}');
});

it('records a denied call', function () {
    ai_test_run_ability('alma/query-leads');

    expect(ToolCall::query()->sole()->outcome)->toBe(ToolCall::DENIED);
});

it('records a failed call with its error', function () {
    ai_test_run_ability('alma/describe-page', ['id' => 404]);

    $call = ToolCall::query()->sole();

    expect($call->outcome)->toBe(ToolCall::FAILED)
        ->and($call->error)->toContain('No page');
});

it('records the first half of a confirmation as awaiting confirmation', function () {
    $this->user->add_cap('alma_manage_redirects');
    $redirect = app(RedirectManager::class)->create('/old/', '/new/');

    ai_test_run_ability('alma/delete-redirect', ['id' => $redirect->id]);

    expect(ToolCall::query()->sole()->outcome)->toBe(ToolCall::AWAITING_CONFIRMATION);
});

it('records invalid input', function () {
    app(ToolAuditLog::class)->recordInvalid(new WP_Error('ability_invalid_input', 'id must be an integer'), ['id' => 'x'], 'alma/describe-page');

    expect(ToolCall::query()->sole())->outcome->toBe(ToolCall::INVALID)->error->toBe('id must be an integer');
});

it('never stores secrets or file contents, and cuts long text', function () {
    $summary = ToolAuditLog::summarize([
        'url' => 'https://example.test/a.png',
        'data' => base64_encode(str_repeat('x', 3000)),
        'password' => 'hunter2',
        'confirmation_token' => '123.abc',
        'nested' => ['api_key' => 'sk-live', 'note' => str_repeat('long ', 100)],
    ]);

    expect($summary)->toContain('https://example.test/a.png')
        ->not->toContain('hunter2')
        ->not->toContain('sk-live')
        ->not->toContain('123.abc')
        ->not->toContain(str_repeat('x', 50))
        ->toContain('[redacted]')
        ->toContain('(500 chars)');
});

it('skips the adapter\'s own meta-tools', function () {
    app(ToolAuditLog::class)->recordResult(['ok'], 'mcp-adapter/execute-ability', []);

    expect(ToolCall::query()->count())->toBe(0);
});

it('prunes rows past the retention window now and then', function () {
    ToolCall::query()->create(['ability' => 'alma/old', 'outcome' => 'succeeded', 'created_at' => now()->subDays(91)]);
    ToolCall::query()->create(['ability' => 'alma/recent', 'outcome' => 'succeeded', 'created_at' => now()->subDays(10)]);
    config(['ai.audit.prune_odds' => 1]);

    app(ToolAuditLog::class)->recordResult(['ok'], 'alma/new', []);

    expect(ToolCall::query()->pluck('ability')->sort()->values()->all())->toBe(['alma/new', 'alma/recent']);
});

it('never fails the call when the log cannot be written', function () {
    Schema::drop('alma_ai_tool_calls');

    expect(app(ToolAuditLog::class)->recordResult(['ok'], 'alma/list-pages', []))->toBe(['ok']);
});

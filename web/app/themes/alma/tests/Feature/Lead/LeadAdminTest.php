<?php

use App\Domains\Lead\Actions\ChangeLeadStatusAction;
use App\Domains\Lead\Deploy\GrantLeadCapability;
use App\Domains\Lead\Hooks\LeadPrivacy;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadCsv;
use App\Domains\Lead\Services\LeadQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('changes a lead\'s status and logs who did it', function () {
    $lead = Lead::query()->create(['email' => 'ada@example.com']);

    app(ChangeLeadStatusAction::class)->execute($lead, LeadStatus::Qualified, 'admin:jane');

    $entry = LeadActivity::query()->where('lead_id', $lead->id)->sole();

    expect($lead->fresh()->status)->toBe(LeadStatus::Qualified)
        ->and($entry->actor)->toBe('admin:jane')
        ->and($entry->detail)->toBe(['from' => 'new', 'to' => 'qualified']);
});

it('filters the list and hides spam unless asked', function () {
    Lead::query()->create(['email' => 'ada@example.com', 'name' => 'Ada', 'form_key' => 'contact']);
    Lead::query()->create(['email' => 'grace@example.com', 'name' => 'Grace', 'form_key' => 'demo']);
    Lead::query()->create(['email' => 'bot@example.com', 'status' => LeadStatus::Spam]);

    $query = app(LeadQuery::class);

    expect($query->build([])->pluck('email')->all())->toEqualCanonicalizing(['ada@example.com', 'grace@example.com'])
        ->and($query->build(['search' => 'grace'])->pluck('email')->all())->toBe(['grace@example.com'])
        ->and($query->build(['form' => 'contact'])->pluck('email')->all())->toBe(['ada@example.com'])
        ->and($query->build(['status' => 'spam'])->pluck('email')->all())->toBe(['bot@example.com'])
        ->and($query->forms())->toBe(['contact', 'default', 'demo']);
});

it('exports exactly the filtered leads', function () {
    Lead::query()->create(['email' => 'ada@example.com', 'form_key' => 'contact']);
    Lead::query()->create(['email' => 'grace@example.com', 'form_key' => 'demo']);

    $stream = fopen('php://memory', 'w+');
    app(LeadCsv::class)->write(app(LeadQuery::class)->build(['form' => 'demo']), $stream);
    rewind($stream);
    $lines = array_filter(explode("\n", stream_get_contents($stream)));

    expect($lines)->toHaveCount(2)
        ->and($lines[1])->toContain('grace@example.com');
});

it('grants the Leads capability idempotently', function () {
    $task = new GrantLeadCapability;

    $task->run();
    $task->run();

    expect(get_role('administrator')->has_cap(GrantLeadCapability::CAPABILITY))->toBeTrue()
        ->and(get_role('editor')->has_cap(GrantLeadCapability::CAPABILITY))->toBeTrue()
        ->and(get_role('author')->has_cap(GrantLeadCapability::CAPABILITY))->toBeFalse();
});

it('exports and erases a person\'s leads for WordPress privacy requests', function () {
    $lead = Lead::query()->create(['email' => 'ada@example.com', 'name' => 'Ada', 'message' => 'Hi']);
    LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Lead', 'outcome' => 'dispatched', 'created_at' => now()]);
    Lead::query()->create(['email' => 'grace@example.com']);

    $privacy = app(LeadPrivacy::class);
    $export = $privacy->export('ADA@example.com');

    expect($export['done'])->toBeTrue()
        ->and($export['data'])->toHaveCount(1)
        ->and(collect($export['data'][0]['data'])->pluck('value'))->toContain('Ada', 'Hi');

    expect($privacy->erase('ada@example.com'))->toMatchArray(['items_removed' => true, 'done' => true])
        ->and(Lead::query()->pluck('email')->all())->toBe(['grace@example.com'])
        ->and(LeadActivity::query()->count())->toBe(0);
});

it('registers the exporter and eraser with WordPress', function () {
    expect(apply_filters('wp_privacy_personal_data_exporters', []))->toHaveKey('alma-leads')
        ->and(apply_filters('wp_privacy_personal_data_erasers', []))->toHaveKey('alma-leads');
});

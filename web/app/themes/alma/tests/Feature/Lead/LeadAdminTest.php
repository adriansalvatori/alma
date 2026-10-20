<?php

use App\Domains\Lead\Actions\ChangeLeadStatusAction;
use App\Domains\Lead\Admin\LeadsAdmin;
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

it('filters by first-touch channel and by rolling date window', function () {
    Lead::query()->create(['email' => 'ada@example.com', 'attribution' => ['first' => ['source' => 'google'], 'last' => null]]);
    Lead::query()->create(['email' => 'grace@example.com', 'attribution' => ['first' => ['source' => null, 'referrer' => null], 'last' => null]]);
    Lead::query()->create(['email' => 'old@example.com', 'created_at' => now()->subDays(40)]);

    $query = app(LeadQuery::class);

    expect($query->build(['source' => 'google'])->pluck('email')->all())->toBe(['ada@example.com'])
        ->and($query->build(['source' => LeadQuery::DIRECT])->pluck('email')->all())->toEqualCanonicalizing(['grace@example.com', 'old@example.com'])
        ->and($query->build(['date_range' => 'month'])->pluck('email')->all())->toEqualCanonicalizing(['ada@example.com', 'grace@example.com'])
        ->and($query->build(['date_range' => 'forever'])->count())->toBe(3)
        ->and($query->sources())->toBe([LeadQuery::DIRECT => 2, 'google' => 1]);
});

describe('the screens', function () {
    beforeEach(function () {
        $this->actingAsWordPressUser(['role' => 'administrator'])->add_cap(LeadsAdmin::CAPABILITY);
    });

    function renderLeadScreen(string $method, array $query = []): string
    {
        $_GET = $query;
        ob_start();

        try {
            app(LeadsAdmin::class)->{$method}();
        } finally {
            $_GET = [];
        }

        return (string) ob_get_clean();
    }

    it('lists leads under KPI cards that filter, with their channel and audit count', function () {
        $lead = Lead::query()->create(['name' => 'Ada Lovelace', 'email' => 'ada@analytical.co.uk', 'company' => 'Engines', 'form_key' => 'contact', 'status' => LeadStatus::Qualified, 'attribution' => ['first' => ['source' => 'google', 'medium' => 'cpc', 'campaign' => 'autumn'], 'last' => null]]);
        LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Lead', 'outcome' => LeadActivity::DISPATCHED, 'created_at' => now()]);
        Lead::query()->create(['name' => 'Bot', 'email' => 'bot@example.com', 'status' => LeadStatus::Spam]);

        $html = renderLeadScreen('renderLeads');

        expect($html)->toContain('class="wrap alma-admin"')
            ->and($html)->toContain('alma-stats-grid')
            ->and($html)->toContain('Total submissions')
            ->and($html)->toContain('alma-tab alma-tab--active')
            ->and($html)->toContain('Ada Lovelace')
            ->and($html)->toContain('google / cpc')
            ->and($html)->toContain('cmp: autumn')
            ->and($html)->toContain('1 event')
            ->and($html)->toContain('domain=analytical.co.uk')
            ->and($html)->not->toContain('>Bot<');

        expect(renderLeadScreen('renderLeads', ['status' => 'qualified']))->toMatch('/alma-stat alma-stat--active[^>]*>\s*<div class="alma-stat__head">\s*<span class="alma-stat__label">Qualified/');
    });

    it('paginates on paged, keeping the screen and its filters', function () {
        foreach (range(1, 21) as $i) {
            Lead::query()->create(['email' => "lead{$i}@example.com", 'form_key' => 'contact']);
        }

        $html = renderLeadScreen('renderLeads', ['form' => 'contact']);

        expect($html)->toContain('paged=2')
            ->and($html)->toContain('page=alma-leads')
            ->and($html)->toContain('form=contact');
    });

    it('shows a lead\'s dossier with its timeline', function () {
        $lead = Lead::query()->create(['name' => 'Ada', 'email' => 'ada@example.com', 'attribution' => ['first' => ['source' => 'google', 'gclid' => str_repeat('x', 120)], 'last' => null]]);
        LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Webhook', 'outcome' => LeadActivity::FAILED, 'detail' => ['status' => 500], 'created_at' => now()]);

        $html = renderLeadScreen('renderLeads', ['lead' => $lead->id]);

        expect($html)->toContain('alma-record-header')
            ->and($html)->toContain('Execution &amp; audit timeline')
            ->and($html)->toContain('alma-timeline__dot--failed')
            ->and($html)->toContain('&quot;status&quot;: 500')
            ->and($html)->toContain('…xxxxxxxx')
            ->and($html)->toContain('alma_lead_delete');
    });

    it('lists the activity log across every lead, filterable', function () {
        $lead = Lead::query()->create(['name' => 'Ada', 'email' => 'ada@example.com']);
        LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Webhook', 'outcome' => LeadActivity::FAILED, 'created_at' => now()]);
        LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'StatusChanged', 'actor' => 'admin:jane', 'outcome' => LeadActivity::SUCCEEDED, 'created_at' => now()]);
        LeadActivity::query()->create(['lead_id' => 999, 'event' => 'LeadCaptured', 'actor' => 'Email', 'outcome' => LeadActivity::SUCCEEDED, 'created_at' => now()]);

        $html = renderLeadScreen('renderActivity');

        expect($html)->toContain('Live activity &amp; audit log')
            ->and($html)->toContain('admin:jane')
            ->and($html)->toContain('Lead #999 (deleted)')
            ->and($html)->toContain('<strong>3</strong>');

        expect(renderLeadScreen('renderActivity', ['outcome' => 'failed']))->toContain('<strong>1</strong>')
            ->not->toContain('admin:jane</strong>');
    });
});

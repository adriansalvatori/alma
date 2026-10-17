<?php

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Models\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsWordPressUser(['role' => 'editor', 'user_login' => 'ai-agent'])->add_cap('alma_manage_leads');
});

function ai_lead(array $attributes = []): Lead
{
    return Lead::query()->create(array_merge([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'form_key' => 'contact',
    ], $attributes));
}

it('counts the period\'s leads by status, form and source, keeping spam out of the totals', function () {
    ai_lead(['form_key' => 'pricing', 'attribution' => ['first' => ['source' => 'Google']]]);
    ai_lead(['form_key' => 'pricing', 'attribution' => ['first' => ['gclid' => 'abc']]]);
    ai_lead(['attribution' => ['first' => ['referrer' => 'https://news.example.org/post']]]);
    ai_lead(['status' => LeadStatus::Qualified]);
    ai_lead(['status' => LeadStatus::Spam, 'attribution' => ['first' => ['source' => 'bots']]]);
    tap(ai_lead(), fn (Lead $old) => $old->forceFill(['created_at' => now()->subDays(40)])->save());

    $stats = ai_test_run_ability('alma/lead-stats', ['days' => 30]);
    $sorted = function (object $counts): array {
        $counts = (array) $counts;
        ksort($counts);

        return $counts;
    };

    expect($stats['total'])->toBe(4)
        ->and($sorted($stats['by_status']))->toBe(['new' => 3, 'qualified' => 1, 'spam' => 1])
        ->and($sorted($stats['by_form']))->toBe(['contact' => 2, 'pricing' => 2])
        ->and($sorted($stats['by_source']))->toBe(['direct' => 1, 'google' => 1, 'google (paid)' => 1, 'news.example.org' => 1]);
});

it('narrows the stats to one form', function () {
    ai_lead(['form_key' => 'pricing']);
    ai_lead(['form_key' => 'contact']);

    expect(ai_test_run_ability('alma/lead-stats', ['form' => 'pricing'])['total'])->toBe(1);
});

it('finds leads with the Leads screen\'s filters, newest first', function () {
    ai_lead(['name' => 'Grace Hopper', 'email' => 'grace@example.com', 'form_key' => 'pricing']);
    ai_lead(['name' => 'Spam Bot', 'email' => 'bot@example.com', 'status' => LeadStatus::Spam]);
    ai_lead(['name' => 'Old Lead', 'email' => 'old@example.com'])->forceFill(['created_at' => Carbon::parse('2026-01-01')])->save();

    $all = ai_test_run_ability('alma/query-leads');
    $grace = ai_test_run_ability('alma/query-leads', ['search' => 'grace']);
    $spam = ai_test_run_ability('alma/query-leads', ['status' => 'spam']);
    $recent = ai_test_run_ability('alma/query-leads', ['since' => now()->subDay()->toDateString()]);

    expect(array_column($all, 'name'))->toBe(['Grace Hopper', 'Old Lead'])
        ->and($grace)->toHaveCount(1)
        ->and($grace[0])->toHaveKeys(['id', 'email', 'phone', 'form', 'status', 'source', 'created_at'])
        ->and($grace[0]['email'])->toBe('grace@example.com')
        ->and(array_column($spam, 'name'))->toBe(['Spam Bot'])
        ->and(array_column($recent, 'name'))->toBe(['Grace Hopper']);
});

it('caps the number of leads returned', function () {
    foreach (range(1, 5) as $i) {
        ai_lead(['email' => "lead{$i}@example.com"]);
    }

    expect(ai_test_run_ability('alma/query-leads', ['limit' => 2]))->toHaveCount(2);
});

it('changes a lead\'s status and records who did it', function () {
    $lead = ai_lead();

    $result = ai_test_run_ability('alma/update-lead-status', ['id' => $lead->uuid, 'status' => 'qualified']);

    expect($result)->toBe(['id' => $lead->uuid, 'from' => 'new', 'to' => 'qualified'])
        ->and($lead->fresh()->status)->toBe(LeadStatus::Qualified)
        ->and(LeadActivity::query()->where('lead_id', $lead->id)->where('event', 'StatusChanged')->value('actor'))->toBe('ai:ai-agent');
});

it('reports an unknown lead instead of guessing', function () {
    expect(ai_test_run_ability('alma/update-lead-status', ['id' => 'nope', 'status' => 'won']))->toBeInstanceOf(WP_Error::class);
});

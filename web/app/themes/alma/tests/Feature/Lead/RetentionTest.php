<?php

use App\Domains\Lead\Hooks\LeadRetention;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Services\LeadPurger;
use App\Domains\Lead\Services\LeadSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

function leadAgedDays(int $days): Lead
{
    $lead = Lead::query()->create(['email' => "lead{$days}@example.com"]);
    $lead->forceFill(['created_at' => Carbon::now()->subDays($days)])->save();
    LeadActivity::query()->create(['lead_id' => $lead->id, 'event' => 'LeadCaptured', 'actor' => 'Lead', 'outcome' => 'dispatched', 'created_at' => now()]);

    return $lead;
}

it('deletes leads and their activity past the retention window', function () {
    app(LeadSettings::class)->save(['retention_days' => 60]);
    $old = leadAgedDays(61);
    $recent = leadAgedDays(59);

    expect(app(LeadPurger::class)->purge())->toBe(1)
        ->and(Lead::query()->pluck('id')->all())->toBe([$recent->id])
        ->and(LeadActivity::query()->where('lead_id', $old->id)->exists())->toBeFalse();
});

it('never keeps leads for less than 30 days', function () {
    app(LeadSettings::class)->save(['retention_days' => 5]);
    leadAgedDays(10);

    expect(app(LeadSettings::class)->retentionDays())->toBe(LeadSettings::MINIMUM_RETENTION_DAYS)
        ->and(app(LeadPurger::class)->purge())->toBe(0);
});

it('counts without deleting on a dry run', function () {
    config(['leads.retention_days' => '30']);
    leadAgedDays(31);

    $this->artisan('alma:leads:purge', ['--dry-run' => true])->expectsOutputToContain('1 lead(s)')->assertSuccessful();

    expect(Lead::query()->count())->toBe(1);
});

it('purges from the command', function () {
    config(['leads.retention_days' => '30']);
    leadAgedDays(31);

    $this->artisan('alma:leads:purge')->assertSuccessful();

    expect(Lead::query()->count())->toBe(0);
});

it('schedules the daily purge once', function () {
    $retention = app(LeadRetention::class);

    $retention->schedule();
    $first = WordPress::$cron[LeadRetention::HOOK];
    $retention->schedule();

    expect(WordPress::$cron[LeadRetention::HOOK])->toBe($first)
        ->and($first['recurrence'])->toBe('daily');
});

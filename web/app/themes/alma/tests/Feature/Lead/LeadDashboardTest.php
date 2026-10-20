<?php

/*
 * The Leads widgets on the wp-admin dashboard (LeadDashboard) and the helpers behind them: the
 * hourly trend and the avatar.
 */

use App\Domains\Lead\Admin\LeadDashboard;
use App\Domains\Lead\Admin\LeadsAdmin;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\HourlyLeadTrend;
use App\Domains\Lead\Services\LeadAvatar;
use App\Domains\Lead\Services\LeadQuery;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAsWordPressUser(['role' => 'administrator'])->add_cap(LeadsAdmin::CAPABILITY);
});

/**
 * @param  array<string, mixed>  $attributes
 */
function dashboardLead(array $attributes = []): Lead
{
    $lead = Lead::query()->create(['email' => 'lead-'.uniqid().'@example.com'] + $attributes);

    if (isset($attributes['created_at'])) {
        $lead->forceFill(['created_at' => $attributes['created_at']])->saveQuietly();
    }

    return $lead;
}

function renderLeadWidget(string $method): string
{
    ob_start();
    app(LeadDashboard::class)->{$method}();

    return (string) ob_get_clean();
}

it('adds the lead widgets to the left column for whoever manages leads', function () {
    app(LeadDashboard::class)->setup();

    expect(WordPress::$options['__dashboard_widgets'])->toHaveKeys(['alma_dashboard_lead_kpis', 'alma_dashboard_lead_hourly', 'alma_dashboard_lead_channels', 'alma_dashboard_lead_recent'])
        ->and(WordPress::$options['__dashboard_widgets']['alma_dashboard_lead_kpis']['context'])->toBe('normal');

    WordPress::$options['__dashboard_widgets'] = [];
    $this->actingAsWordPressUser(['role' => 'author']);
    app(LeadDashboard::class)->setup();

    expect(WordPress::$options['__dashboard_widgets'])->toBe([]);
});

it('counts the last week\'s leads as one cohort, spam and older leads left out', function () {
    dashboardLead(['status' => LeadStatus::New]);
    dashboardLead(['status' => LeadStatus::Contacted]);
    dashboardLead(['status' => LeadStatus::Qualified]);
    dashboardLead(['status' => LeadStatus::Won, 'attribution' => ['first' => ['source' => 'google', 'medium' => 'cpc']]]);
    dashboardLead(['status' => LeadStatus::Spam]);
    dashboardLead(['status' => LeadStatus::Won, 'created_at' => now()->subDays(30)]);

    $metrics = app(LeadDashboard::class)->metrics();

    expect($metrics['total'])->toBe(4)
        ->and($metrics['contacted'])->toBe(3)
        ->and($metrics['qualified'])->toBe(2)
        ->and($metrics['won'])->toBe(1)
        ->and($metrics['sources'])->toBe([LeadQuery::DIRECT => 3, 'google' => 1])
        ->and($metrics['daily'])->toHaveCount(LeadDashboard::KPI_WINDOW_DAYS)
        ->and(end($metrics['daily'])['count'])->toBe(4);

    $html = renderLeadWidget('renderKpis');

    expect($html)->toContain('alma-dash-kpi-number">4<')
        ->and($html)->toContain('25%')
        ->and($html)->toContain('alma-dash-barchart-grid');
});

it('links each channel to the list filtered to it', function () {
    dashboardLead(['attribution' => ['first' => ['source' => 'newsletter']]]);
    dashboardLead();

    $html = renderLeadWidget('renderChannels');

    expect($html)->toContain('newsletter')
        ->and($html)->toContain('Direct')
        ->and($html)->toContain('source=newsletter')
        ->and($html)->toContain('alma-dash-stacked-seg');
});

it('lists the latest leads, spam aside', function () {
    dashboardLead(['name' => 'Ada Lovelace', 'company' => 'Analytical Engines']);
    dashboardLead(['name' => 'Bot', 'status' => LeadStatus::Spam]);

    $html = renderLeadWidget('renderRecent');

    expect($html)->toContain('Ada Lovelace')
        ->and($html)->toContain('Analytical Engines')
        ->and($html)->toContain('AL')
        ->and($html)->not->toContain('>Bot<');
});

it('draws the hourly trend for the range in the URL', function () {
    dashboardLead();
    $_GET[LeadDashboard::HOURLY_RANGE_PARAM] = '7d';

    $html = renderLeadWidget('renderHourly');
    unset($_GET[LeadDashboard::HOURLY_RANGE_PARAM]);

    expect($html)->toContain('alma-dash-barchart-hourly')
        ->and($html)->toMatch('/alma-dash-range-tab alma-dash-range-active"[^>]*>Last 7 days/');
});

it('puts live figures on the Leads module card and adds its shortcuts', function () {
    dashboardLead(['status' => LeadStatus::Won]);

    $modules = apply_filters('alma/dashboard/modules', ['lead' => ['title' => 'Lead', 'status' => 'active', 'description' => '', 'chips' => []]]);
    $shortcuts = apply_filters('alma/dashboard/shortcuts', []);

    expect($modules['lead']['chips'])->toContain('Total: 1', 'Won: 1')
        ->and(array_column($shortcuts, 'title'))->toContain('Lead management hub', 'Export all submissions');
});

it('forgets the cached figures as soon as a lead changes', function () {
    Cache::put('alma_lead_kpis', ['stale' => true], 180);
    Cache::put('alma_dashboard_lead_metrics_7d', ['stale' => true], 180);

    dashboardLead();

    expect(Cache::has('alma_lead_kpis'))->toBeFalse()
        ->and(Cache::has('alma_dashboard_lead_metrics_7d'))->toBeFalse();
});

describe('the hourly trend', function () {
    it('counts leads per hour of the site\'s clock and averages them per day', function () {
        $now = CarbonImmutable::parse('2026-10-03 12:30:00', 'UTC');

        foreach (['2026-10-01 09:15:00', '2026-10-02 09:40:00', '2026-10-02 14:05:00'] as $at) {
            dashboardLead(['created_at' => $at]);
        }

        $trend = HourlyLeadTrend::for(Lead::query(), 'month', $now);

        expect($trend['total'])->toBe(3)
            ->and($trend['hours'][9])->toBe(['hour' => 9, 'count' => 2, 'days' => 3, 'average' => 0.7])
            ->and($trend['hours'][14]['days'])->toBe(2)
            ->and($trend['busiest'])->toBe(9)
            ->and($trend['quietest'])->not->toBe(23);
    });

    it('falls back to this month for a range it doesn\'t know', function () {
        expect(HourlyLeadTrend::normalise('yesterday'))->toBe('month')
            ->and(HourlyLeadTrend::hourLabel(0))->toBe('12am')
            ->and(HourlyLeadTrend::hourLabel(13))->toBe('1pm')
            ->and(HourlyLeadTrend::empty('7d')['total'])->toBe(0);
    });
});

describe('the avatar', function () {
    it('shows a company\'s logo, never a mailbox provider\'s', function () {
        expect(LeadAvatar::logoUrl('ada@analytical.co.uk'))->toContain('domain=analytical.co.uk')
            ->and(LeadAvatar::logoUrl('ada@gmail.com'))->toBeNull()
            ->and(LeadAvatar::logoUrl('ada@outlook.es'))->toBeNull()
            ->and(LeadAvatar::logoUrl('not-an-address'))->toBeNull();
    });

    it('falls back to stable, tinted initials', function () {
        expect(LeadAvatar::initials('Ada King Lovelace', null))->toBe('AL')
            ->and(LeadAvatar::initials('', 'grace@example.com'))->toBe('GR')
            ->and(LeadAvatar::tint('ada@example.com'))->toBe(LeadAvatar::tint('ADA@example.com '));
    });
});

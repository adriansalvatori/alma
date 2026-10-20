<?php

namespace App\Domains\Lead\Admin;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\HourlyLeadTrend;
use App\Domains\Lead\Services\LeadAvatar;
use App\Domains\Lead\Services\LeadQuery;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The Leads widgets on the wp-admin dashboard's left column: the last week's KPIs and funnel,
 * leads by hour of the day, the channels that brought them, and the latest leads. Also the
 * module's live figures on the dashboard's Modules card, and its shortcuts.
 *
 * Markup in resources/views/admin/dashboard; each render method gathers what its widget shows and
 * hands it to the view. Every figure leaves spam out. Ported from Remote Leverage's
 * MarketingDashboard.
 */
class LeadDashboard
{
    /**
     * How far back the KPI widget looks: every figure on the card covers the same window as the
     * chart under it, so the card answers one question consistently. Lifetime totals are on the
     * Leads screen, which is where a lifetime number belongs.
     */
    public const KPI_WINDOW_DAYS = 7;

    /** The query argument the hourly trend's range filter sets on the dashboard URL. */
    public const HOURLY_RANGE_PARAM = 'alma_hourly_range';

    /** Channel colours, darkest first, so the biggest share reads first. */
    private const PALETTE = ['#09090b', '#27272a', '#52525b', '#71717a', '#a1a1aa', '#d4d4d8'];

    public function register(): void
    {
        add_action('wp_dashboard_setup', [$this, 'setup'], 999);
        add_filter('alma/dashboard/modules', [$this, 'moduleCard']);
        add_filter('alma/dashboard/shortcuts', [$this, 'shortcuts']);
    }

    public function setup(): void
    {
        if (! current_user_can(LeadsAdmin::CAPABILITY)) {
            return;
        }

        wp_add_dashboard_widget('alma_dashboard_lead_kpis', sprintf(__('Lead performance: last %d days', 'alma'), self::KPI_WINDOW_DAYS), [$this, 'renderKpis'], null, null, 'normal', 'high');
        wp_add_dashboard_widget('alma_dashboard_lead_hourly', __('Hourly lead trend', 'alma'), [$this, 'renderHourly'], null, null, 'normal', 'high');
        wp_add_dashboard_widget('alma_dashboard_lead_channels', __('Traffic acquisition & attribution channels', 'alma'), [$this, 'renderChannels'], null, null, 'normal', 'default');
        wp_add_dashboard_widget('alma_dashboard_lead_recent', __('Recent captured leads', 'alma'), [$this, 'renderRecent'], null, null, 'normal', 'default');
    }

    /**
     * The KPI tiles, the funnel and the daily volume chart, all over KPI_WINDOW_DAYS.
     *
     * The rates are a cohort's: leads captured in the window that have since been qualified or
     * won, over the leads captured in the window. Dividing this week's wins by this week's leads
     * would mix two populations and could pass 100% in a week that closes a backlog.
     */
    public function renderKpis(): void
    {
        $metrics = $this->metrics();
        $total = $metrics['total'];
        $maxDaily = max(1, ...array_column($metrics['daily'], 'count'));

        $bars = array_map(fn (array $day) => [
            'label' => $day['label'],
            'count' => $day['count'],
            'pct' => $day['count'] > 0 ? max(14, (int) round($day['count'] / $maxDaily * 100)) : 0,
            'isPeak' => $day['count'] === $maxDaily && $day['count'] > 0,
        ], $metrics['daily']);

        echo view('lead::admin.dashboard.kpis', [
            'windowDays' => self::KPI_WINDOW_DAYS,
            'total' => $total,
            'contacted' => $metrics['contacted'],
            'qualified' => $metrics['qualified'],
            'won' => $metrics['won'],
            'contactedRate' => $total > 0 ? round($metrics['contacted'] / $total * 100, 1) : 0.0,
            'qualifiedRate' => $total > 0 ? round($metrics['qualified'] / $total * 100, 1) : 0.0,
            'wonRate' => $total > 0 ? round($metrics['won'] / $total * 100, 1) : 0.0,
            'bars' => $bars,
        ])->render();
    }

    /**
     * Leads per hour of the day over a selectable range, to spot the hours campaigns
     * underperform. The range is a query argument on the dashboard URL, so a filtered view can be
     * bookmarked or pasted into a chat. Bars are leads per day at that hour rather than a raw
     * count; see HourlyLeadTrend for why.
     */
    public function renderHourly(): void
    {
        $trend = $this->hourly(HourlyLeadTrend::normalise($_GET[self::HOURLY_RANGE_PARAM] ?? null));
        $hours = $trend['hours'];
        $zone = HourlyLeadTrend::zoneLabel();
        $from = CarbonImmutable::parse($trend['from']);
        $through = CarbonImmutable::parse($trend['to'])->subSecond();
        $maxAverage = max(0.1, ...array_column($hours, 'average'));
        $describe = fn (int $hour): string => sprintf(__('%1$s %2$s (%3$s a day)', 'alma'), HourlyLeadTrend::hourLabel($hour), $zone, number_format_i18n($hours[$hour]['average'], 1));

        $ranges = [];

        foreach (HourlyLeadTrend::ranges() as $slug => $label) {
            $ranges[] = [
                'url' => add_query_arg(self::HOURLY_RANGE_PARAM, $slug, admin_url('index.php')).'#alma_dashboard_lead_hourly',
                'active' => $slug === $trend['range'],
                'label' => $label,
            ];
        }

        $bars = [];

        foreach ($hours as $hour) {
            $bars[] = [
                'count' => $hour['count'],
                'average' => $hour['average'],
                'pct' => $hour['average'] > 0 ? max(8, (int) round($hour['average'] / $maxAverage * 100)) : 0,
                'isPeak' => $hour['hour'] === $trend['busiest'],
                'title' => sprintf(
                    __('%1$s to %2$s %3$s: %4$s a day (%5$d leads over %6$d days)', 'alma'),
                    HourlyLeadTrend::hourLabel($hour['hour']),
                    HourlyLeadTrend::hourLabel(($hour['hour'] + 1) % 24),
                    $zone,
                    number_format_i18n($hour['average'], 1),
                    $hour['count'],
                    $hour['days'],
                ),
                'label' => $hour['hour'] % 3 === 0 ? HourlyLeadTrend::hourLabel($hour['hour']) : null,
            ];
        }

        $described = $trend['busiest'] !== null && $trend['quietest'] !== null;

        echo view('lead::admin.dashboard.hourly', [
            'summary' => sprintf(
                _n('%1$s lead, %2$s to %3$s, hours in %4$s', '%1$s leads, %2$s to %3$s, hours in %4$s', $trend['total'], 'alma'),
                number_format_i18n($trend['total']),
                $from->format('M j'),
                $through->format('M j'),
                $zone,
            ),
            'ranges' => $ranges,
            'bars' => $bars,
            'busiest' => $described ? $describe((int) $trend['busiest']) : null,
            'quietest' => $described ? $describe((int) $trend['quietest']) : null,
        ])->render();
    }

    /**
     * Each first-touch channel's share of the window's leads, with a link to the list filtered
     * to it.
     */
    public function renderChannels(): void
    {
        $metrics = $this->metrics();
        $total = max(1, $metrics['total']);
        $channels = [];
        $index = 0;

        foreach ($metrics['sources'] as $source => $count) {
            $channels[] = [
                'source' => $source === LeadQuery::DIRECT ? __('Direct', 'alma') : $source,
                'count' => $count,
                'pct' => round($count / $total * 100, 1),
                'color' => self::PALETTE[$index++ % count(self::PALETTE)],
                'filterUrl' => LeadsAdmin::url(['source' => $source, 'date_range' => 'week']),
            ];
        }

        echo view('lead::admin.dashboard.channels', ['channels' => $channels, 'windowDays' => self::KPI_WINDOW_DAYS])->render();
    }

    /**
     * The latest leads, with their status and a way into each.
     */
    public function renderRecent(): void
    {
        try {
            $leads = Lead::query()->where('status', '!=', LeadStatus::Spam->value)->latest('created_at')->latest('id')->take(6)->get();
            $total = Lead::query()->where('status', '!=', LeadStatus::Spam->value)->count();
        } catch (Throwable) {
            $leads = collect();
            $total = 0;
        }

        $rows = $leads->map(fn (Lead $lead) => [
            'lead' => $lead,
            'name' => $lead->name !== '' ? $lead->name : __('(no name)', 'alma'),
            'initials' => LeadAvatar::initials($lead->name, $lead->email),
            'tint' => LeadAvatar::tint($lead->email, $lead->name),
            'statusClass' => match ($lead->status) {
                LeadStatus::Won => 'alma-badge-emerald',
                LeadStatus::Qualified, LeadStatus::Contacted => 'alma-badge-amber',
                LeadStatus::New => 'alma-badge-dark',
                default => 'alma-badge-zinc',
            },
            'inspectUrl' => LeadsAdmin::url(['lead' => $lead->id]),
            'timeAgo' => $lead->created_at?->diffForHumans() ?? '',
        ])->all();

        echo view('lead::admin.dashboard.recent', ['rows' => $rows, 'total' => $total])->render();
    }

    /**
     * The Leads card on the dashboard's Modules widget gets the live figures.
     *
     * @param  array<string, array<string, mixed>>  $modules
     * @return array<string, array<string, mixed>>
     */
    public function moduleCard(array $modules): array
    {
        if (! isset($modules['lead']) || ! current_user_can(LeadsAdmin::CAPABILITY)) {
            return $modules;
        }

        try {
            $kpis = app(LeadsAdmin::class)->kpis();
        } catch (Throwable) {
            return $modules;
        }

        $modules['lead']['chips'] = [
            sprintf(__('Total: %s', 'alma'), number_format_i18n($kpis['total'])),
            sprintf(__('Awaiting reply: %s', 'alma'), number_format_i18n($kpis['new'])),
            sprintf(__('Won: %s', 'alma'), number_format_i18n($kpis['won'])),
            sprintf(__('Audit log: %s', 'alma'), number_format_i18n($kpis['logs'])),
        ];

        return $modules;
    }

    /**
     * @param  list<array<string, mixed>>  $shortcuts
     * @return list<array<string, mixed>>
     */
    public function shortcuts(array $shortcuts): array
    {
        if (! current_user_can(LeadsAdmin::CAPABILITY)) {
            return $shortcuts;
        }

        return [
            ...$shortcuts,
            [
                'title' => __('Lead management hub', 'alma'),
                'description' => __('Search, filter & review captured leads', 'alma'),
                'url' => LeadsAdmin::url(),
                'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            ],
            [
                'title' => __('Audit activity trail', 'alma'),
                'description' => __('Every dispatch and integration outcome', 'alma'),
                'url' => LeadsAdmin::url([], LeadsAdmin::ACTIVITY_PAGE),
                'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
            ],
            [
                'title' => __('Export all submissions', 'alma'),
                'description' => __('Download every lead as CSV', 'alma'),
                'url' => wp_nonce_url(add_query_arg(['action' => 'alma_leads_export'], admin_url('admin-post.php')), 'alma_leads_export'),
                'icon' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
            ],
        ];
    }

    /**
     * The window's figures, cached for three minutes (and cleared when a lead changes).
     *
     * @return array{total: int, contacted: int, qualified: int, won: int, sources: array<string, int>, daily: list<array{date: string, label: string, count: int}>}
     */
    public function metrics(): array
    {
        try {
            return Cache::remember('alma_dashboard_lead_metrics_7d', 180, fn (): array => $this->gatherMetrics());
        } catch (Throwable) {
            return ['total' => 0, 'contacted' => 0, 'qualified' => 0, 'won' => 0, 'sources' => [], 'daily' => $this->days([])];
        }
    }

    /**
     * @return array{total: int, contacted: int, qualified: int, won: int, sources: array<string, int>, daily: list<array{date: string, label: string, count: int}>}
     */
    private function gatherMetrics(): array
    {
        $since = now()->subDays(self::KPI_WINDOW_DAYS - 1)->startOfDay();
        $window = fn (): Builder => Lead::query()->where('status', '!=', LeadStatus::Spam->value)->where('created_at', '>=', $since);

        $byStatus = $window()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->map(fn ($n) => (int) $n)->all();
        $count = fn (LeadStatus ...$statuses) => array_sum(array_map(fn (LeadStatus $status) => $byStatus[$status->value] ?? 0, $statuses));

        $sources = [];

        foreach ($window()->toBase()->pluck('attribution') as $json) {
            $attribution = is_string($json) ? json_decode($json, true) : null;
            $source = is_array($attribution) ? ($attribution['first']['source'] ?? null) : null;
            $key = is_string($source) && $source !== '' ? $source : LeadQuery::DIRECT;
            $sources[$key] = ($sources[$key] ?? 0) + 1;
        }

        arsort($sources);

        $daily = $window()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->map(fn ($n) => (int) $n)
            ->all();

        return [
            'total' => array_sum($byStatus),
            // Anyone the team has touched: answered, qualified, won or lost.
            'contacted' => $count(LeadStatus::Contacted, LeadStatus::Qualified, LeadStatus::Won, LeadStatus::Lost),
            'qualified' => $count(LeadStatus::Qualified, LeadStatus::Won),
            'won' => $count(LeadStatus::Won),
            'sources' => array_slice($sources, 0, 6, true),
            'daily' => $this->days($daily),
        ];
    }

    /**
     * Every day of the window, oldest first, with its count (zero when nothing came in).
     *
     * @param  array<string, int>  $counts
     * @return list<array{date: string, label: string, count: int}>
     */
    private function days(array $counts): array
    {
        $days = [];

        for ($i = self::KPI_WINDOW_DAYS - 1; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $days[] = ['date' => $day->format('Y-m-d'), 'label' => $day->format('D'), 'count' => $counts[$day->format('Y-m-d')] ?? 0];
        }

        return $days;
    }

    /**
     * The hourly trend for one range, cached under a key of its own so switching the filter never
     * shows another range's figures.
     *
     * @return array<string, mixed>
     */
    private function hourly(string $range): array
    {
        try {
            return Cache::remember('alma_dashboard_lead_hourly_'.$range, 180, fn () => HourlyLeadTrend::for(
                Lead::query()->where('status', '!=', LeadStatus::Spam->value),
                $range,
            ));
        } catch (Throwable) {
            return HourlyLeadTrend::empty($range);
        }
    }
}

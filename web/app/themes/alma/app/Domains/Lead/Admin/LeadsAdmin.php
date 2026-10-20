<?php

namespace App\Domains\Lead\Admin;

use App\Domains\Lead\Actions\ChangeLeadStatusAction;
use App\Domains\Lead\Deploy\GrantLeadCapability;
use App\Domains\Lead\Listeners\SendLeadWebhook;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadCsv;
use App\Domains\Lead\Services\LeadPurger;
use App\Domains\Lead\Services\LeadQuery;
use App\Domains\Lead\Services\LeadSettings;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * wp-admin → Leads: the list with its KPI cards and filters, a lead's dossier and timeline, the
 * activity log across every lead, CSV export, the retention purge and settings.
 *
 * Every screen and action checks the alma_manage_leads capability (granted by the deploy task),
 * and every state change is a nonce-checked POST to admin-post.php.
 */
class LeadsAdmin
{
    public const PAGE = 'alma-leads';

    public const ACTIVITY_PAGE = 'alma-lead-activity';

    public const SETTINGS_PAGE = 'alma-lead-settings';

    public const CAPABILITY = GrantLeadCapability::CAPABILITY;

    private const PER_PAGE = 20;

    private const ACTIVITY_PER_PAGE = 30;

    public function __construct(
        private LeadQuery $leads,
        private LeadSettings $settings,
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_bar_menu', [$this, 'toolbar'], 1000);
        add_action('admin_post_alma_lead_status', [$this, 'updateStatus']);
        add_action('admin_post_alma_lead_delete', [$this, 'delete']);
        add_action('admin_post_alma_leads_export', [$this, 'export']);
        add_action('admin_post_alma_leads_purge', [$this, 'purge']);
        add_action('admin_post_alma_lead_settings', [$this, 'saveSettings']);
        add_action('admin_post_alma_lead_webhook_test', [$this, 'testWebhook']);
    }

    /**
     * @param  array<string, scalar>  $args
     */
    public static function url(array $args = [], string $page = self::PAGE): string
    {
        return add_query_arg(['page' => $page] + $args, admin_url('admin.php'));
    }

    /**
     * The Leads section's screens, as <x-admin::tabs> draws them.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function tabs(): array
    {
        return [
            'leads' => [__('All leads & submissions', 'alma'), self::url()],
            'activity' => [__('Live activity & audit log', 'alma'), self::url([], self::ACTIVITY_PAGE)],
            'settings' => [__('Form & routing settings', 'alma'), self::url([], self::SETTINGS_PAGE)],
        ];
    }

    public function menu(): void
    {
        $badge = ($new = $this->newLeadCount()) > 0 ? sprintf(' <span class="awaiting-mod">%d</span>', $new) : '';

        add_menu_page(__('Leads', 'alma'), __('Leads', 'alma').$badge, self::CAPABILITY, self::PAGE, [$this, 'renderLeads'], 'dashicons-groups', 26);
        add_submenu_page(self::PAGE, __('Leads', 'alma'), __('All leads', 'alma'), self::CAPABILITY, self::PAGE, [$this, 'renderLeads']);
        add_submenu_page(self::PAGE, __('Lead activity', 'alma'), __('Activity log', 'alma'), self::CAPABILITY, self::ACTIVITY_PAGE, [$this, 'renderActivity']);
        add_submenu_page(self::PAGE, __('Lead settings', 'alma'), __('Settings', 'alma'), self::CAPABILITY, self::SETTINGS_PAGE, [$this, 'renderSettings']);
    }

    /**
     * "Leads & submissions" in the toolbar's brand menu, for whoever may open it.
     */
    public function toolbar(\WP_Admin_Bar $toolbar): void
    {
        if (! current_user_can(self::CAPABILITY) || ! $toolbar->get_node('site-name')) {
            return;
        }

        $toolbar->add_node([
            'id' => 'alma-sub-leads',
            'parent' => 'site-name',
            'title' => esc_html__('Leads & submissions', 'alma'),
            'href' => self::url(),
        ]);
    }

    public function renderLeads(): void
    {
        $this->authorize();

        if (isset($_GET['lead'])) {
            $this->renderLead(absint($_GET['lead']));

            return;
        }

        $filters = $this->filters($_GET);
        $query = $this->leads->build($filters)->withCount('activity');

        echo view('lead::admin.index', [
            'leads' => $this->paginate($query, self::PER_PAGE, self::PAGE, $this->queryArgs($filters)),
            'filters' => $filters,
            'kpis' => $this->kpis(),
            'statuses' => LeadStatus::cases(),
            'forms' => $this->leads->forms(),
            'sources' => array_keys($this->leads->sources()),
            'dateRanges' => [
                '24h' => __('Last 24 hours', 'alma'),
                'week' => __('Last 7 days', 'alma'),
                'month' => __('Last 30 days', 'alma'),
            ],
            'exportUrl' => wp_nonce_url(add_query_arg(['action' => 'alma_leads_export'] + $this->queryArgs($filters), admin_url('admin-post.php')), 'alma_leads_export'),
            'retentionDays' => $this->settings->retentionDays(),
            'purged' => isset($_GET['purged']) ? absint($_GET['purged']) : null,
            'deleted' => isset($_GET['deleted']),
        ])->render();
    }

    public function renderActivity(): void
    {
        $this->authorize();

        $filters = array_filter([
            'search' => sanitize_text_field(wp_unslash((string) ($_GET['s'] ?? ''))),
            'event' => sanitize_text_field(wp_unslash((string) ($_GET['event'] ?? ''))),
            'actor' => sanitize_text_field(wp_unslash((string) ($_GET['actor'] ?? ''))),
            'outcome' => sanitize_key((string) ($_GET['outcome'] ?? '')),
        ]);

        $query = LeadActivity::query()->with('lead')->latest('id');

        if (isset($filters['search'])) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $filters['search']).'%';
            $query->where(fn (Builder $q) => $q
                ->where('event', 'like', $like)
                ->orWhere('actor', 'like', $like)
                ->orWhereHas('lead', fn (Builder $lead) => $lead->where('email', 'like', $like)->orWhere('name', 'like', $like)));
        }

        foreach (['event', 'actor', 'outcome'] as $column) {
            if (isset($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        echo view('lead::admin.activity', [
            'logs' => $this->paginate($query, self::ACTIVITY_PER_PAGE, self::ACTIVITY_PAGE, array_filter([
                's' => $filters['search'] ?? null,
                'event' => $filters['event'] ?? null,
                'actor' => $filters['actor'] ?? null,
                'outcome' => $filters['outcome'] ?? null,
            ])),
            'filters' => $filters,
            // Read off the table rather than listed by hand, so a new integration is filterable
            // the day it starts logging.
            'events' => LeadActivity::query()->distinct()->orderBy('event')->pluck('event')->filter()->values()->all(),
            'actors' => LeadActivity::query()->distinct()->orderBy('actor')->pluck('actor')->filter()->values()->all(),
            'outcomes' => [LeadActivity::DISPATCHED, LeadActivity::SUCCEEDED, LeadActivity::FAILED, LeadActivity::SKIPPED],
        ])->render();
    }

    public function renderSettings(): void
    {
        $this->authorize();

        echo view('lead::admin.settings', [
            'settings' => $this->settings,
            'notice' => sanitize_key($_GET['notice'] ?? ''),
            'status' => absint($_GET['status'] ?? 0),
        ])->render();
    }

    public function updateStatus(): void
    {
        $this->authorize();

        $id = absint($_POST['lead'] ?? 0);
        check_admin_referer('alma_lead_status_'.$id);

        $lead = Lead::query()->find($id);
        $status = LeadStatus::tryFrom(sanitize_key($_POST['status'] ?? ''));

        if ($lead !== null && $status !== null) {
            app(ChangeLeadStatusAction::class)->execute($lead, $status, 'admin:'.wp_get_current_user()->user_login);
        }

        $this->redirect(self::url(['lead' => $id, 'updated' => 1]));
    }

    /**
     * Delete one lead and its activity: test submissions, duplicates, a person who asked. Tools →
     * Erase Personal Data does the same for every lead an address sent.
     */
    public function delete(): void
    {
        $this->authorize();

        $id = absint($_POST['lead'] ?? 0);
        check_admin_referer('alma_lead_delete_'.$id);

        LeadActivity::query()->where('lead_id', $id)->delete();
        Lead::query()->whereKey($id)->first()?->delete();

        $this->redirect(self::url(['deleted' => 1]));
    }

    public function export(): void
    {
        $this->authorize();
        check_admin_referer('alma_leads_export');

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="leads-'.gmdate('Y-m-d').'.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM, so Excel reads accents correctly.
        app(LeadCsv::class)->write($this->leads->build($this->filters($_GET)), $out);
        fclose($out);

        exit;
    }

    /**
     * Run the retention purge now instead of waiting for tonight's cron: the same window, the
     * same batches, the same 30-day floor (LeadPurger, LeadSettings).
     */
    public function purge(): void
    {
        $this->authorize();
        check_admin_referer('alma_leads_purge');

        $purged = app(LeadPurger::class)->purge();
        Lead::flushMetrics();

        $this->redirect(self::url(['purged' => $purged]));
    }

    public function saveSettings(): void
    {
        $this->authorize();
        check_admin_referer('alma_lead_settings');

        $input = wp_unslash($_POST['settings'] ?? []);
        $this->settings->save(is_array($input) ? $input : []);

        $this->redirect(self::url(['notice' => 'saved'], self::SETTINGS_PAGE));
    }

    /**
     * Send a sample lead to the webhook right now, so whoever sets up the integration sees the
     * real payload and whether the endpoint accepts it, without submitting a form.
     */
    public function testWebhook(): void
    {
        $this->authorize();
        check_admin_referer('alma_lead_webhook_test');

        $url = $this->settings->webhookUrl();

        if ($url === '') {
            $this->redirect(self::url(['notice' => 'webhook-missing'], self::SETTINGS_PAGE));
        }

        $sample = new Lead([
            'name' => 'Test Lead',
            'email' => 'test-lead@example.com',
            'company' => 'Example Co',
            'message' => 'A test sent from Leads → Settings.',
            'form_key' => 'test',
            'consent' => true,
            'status' => LeadStatus::New,
        ]);
        $sample->uuid = '00000000-0000-0000-0000-000000000000';
        $sample->created_at = now();

        $result = app(SendLeadWebhook::class)->deliver($sample, $url, 'lead.test');

        $this->redirect(self::url([
            'notice' => $result['ok'] ? 'webhook-ok' : 'webhook-failed',
            'status' => $result['status'] ?? 0,
        ], self::SETTINGS_PAGE));
    }

    /**
     * The KPI cards' counts: the whole table (spam aside) by stage, and the audit log's size.
     * Cached for three minutes, and cleared whenever a lead changes (Lead::flushMetrics()).
     *
     * @return array{total: int, new: int, qualified: int, won: int, spam: int, logs: int}
     */
    public function kpis(): array
    {
        return Cache::remember('alma_lead_kpis', 180, function (): array {
            $counts = $this->leads->statusCounts();

            return [
                'total' => array_sum(array_diff_key($counts, [LeadStatus::Spam->value => 0])),
                'new' => $counts[LeadStatus::New->value] ?? 0,
                'qualified' => $counts[LeadStatus::Qualified->value] ?? 0,
                'won' => $counts[LeadStatus::Won->value] ?? 0,
                'spam' => $counts[LeadStatus::Spam->value] ?? 0,
                'logs' => LeadActivity::query()->count(),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    private function filters(array $input): array
    {
        $dateRange = sanitize_key((string) ($input['date_range'] ?? ''));

        return array_filter([
            'search' => sanitize_text_field(wp_unslash((string) ($input['s'] ?? ''))),
            'status' => sanitize_key((string) ($input['status'] ?? '')),
            'form' => sanitize_key((string) ($input['form'] ?? '')),
            'source' => sanitize_text_field(wp_unslash((string) ($input['source'] ?? ''))),
            'date_range' => array_key_exists($dateRange, LeadQuery::DATE_RANGES) ? $dateRange : '',
        ]);
    }

    /**
     * The filters as the URL spells them (`s` for search), to carry across pages and into the
     * export.
     *
     * @param  array<string, string>  $filters
     * @return array<string, string>
     */
    private function queryArgs(array $filters): array
    {
        $args = $filters;

        if (isset($args['search'])) {
            $args = ['s' => $args['search']] + $args;
            unset($args['search']);
        }

        return $args;
    }

    /**
     * Paginate a query for a wp-admin screen.
     *
     * Laravel's page parameter is `page`, which inside wp-admin is the screen's slug
     * (`admin.php?page=alma-leads`): left at the default, every list is stuck on page 1 and its
     * Next link points at `admin.php?page=2`. So the cursor is `paged` (WordPress' own name for
     * it), and the links keep the screen and its filters.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $args
     */
    private function paginate(Builder $query, int $perPage, string $page, array $args): LengthAwarePaginator
    {
        return $query->paginate($perPage, ['*'], 'paged', max(1, absint($_GET['paged'] ?? 1)))
            ->withPath(admin_url('admin.php'))
            ->appends(['page' => $page] + array_filter($args, fn ($value) => $value !== '' && $value !== null));
    }

    private function renderLead(int $id): void
    {
        $lead = Lead::query()->with('activity')->find($id);

        if ($lead === null) {
            wp_die(esc_html__('That lead no longer exists.', 'alma'), '', ['response' => 404, 'back_link' => true]);
        }

        echo view('lead::admin.show', [
            'lead' => $lead,
            'statuses' => LeadStatus::cases(),
            'updated' => isset($_GET['updated']),
        ])->render();
    }

    /**
     * The menu badge runs on every wp-admin page, so it must never be what breaks wp-admin: before
     * the first deploy has migrated, the table does not exist yet.
     */
    private function newLeadCount(): int
    {
        if (! current_user_can(self::CAPABILITY)) {
            return 0;
        }

        try {
            return Lead::query()->where('status', LeadStatus::New->value)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private function authorize(): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('Sorry, you are not allowed to manage leads.', 'alma'), '', ['response' => 403]);
        }
    }

    private function redirect(string $url): never
    {
        wp_safe_redirect($url);

        exit;
    }
}

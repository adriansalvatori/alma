<?php

namespace App\Domains\Lead\Admin;

use App\Domains\Lead\Actions\ChangeLeadStatusAction;
use App\Domains\Lead\Deploy\GrantLeadCapability;
use App\Domains\Lead\Listeners\SendLeadWebhook;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadCsv;
use App\Domains\Lead\Services\LeadQuery;
use App\Domains\Lead\Services\LeadSettings;

/**
 * wp-admin → Leads: the list, a lead's detail and activity, CSV export and settings.
 *
 * Every screen and action checks the alma_manage_leads capability (granted by the deploy task),
 * and every state change is a nonce-checked POST to admin-post.php.
 */
class LeadsAdmin
{
    public const PAGE = 'alma-leads';

    public const SETTINGS_PAGE = 'alma-lead-settings';

    public const CAPABILITY = GrantLeadCapability::CAPABILITY;

    public function __construct(
        private LeadQuery $leads,
        private LeadSettings $settings,
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_alma_lead_status', [$this, 'updateStatus']);
        add_action('admin_post_alma_leads_export', [$this, 'export']);
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

    public function menu(): void
    {
        $badge = ($new = $this->newLeadCount()) > 0 ? sprintf(' <span class="awaiting-mod">%d</span>', $new) : '';

        add_menu_page(__('Leads', 'alma'), __('Leads', 'alma').$badge, self::CAPABILITY, self::PAGE, [$this, 'renderLeads'], 'dashicons-email-alt', 26);
        add_submenu_page(self::PAGE, __('Leads', 'alma'), __('All leads', 'alma'), self::CAPABILITY, self::PAGE, [$this, 'renderLeads']);
        add_submenu_page(self::PAGE, __('Lead settings', 'alma'), __('Settings', 'alma'), self::CAPABILITY, self::SETTINGS_PAGE, [$this, 'renderSettings']);
    }

    public function renderLeads(): void
    {
        $this->authorize();

        if (isset($_GET['lead'])) {
            $this->renderLead(absint($_GET['lead']));

            return;
        }

        require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';

        $filters = $this->filters($_GET);
        $table = new LeadsListTable($this->leads, $filters);
        $table->prepare_items();

        echo view('lead::admin.index', [
            'table' => $table,
            'filters' => $filters,
            'exportUrl' => wp_nonce_url(add_query_arg(['action' => 'alma_leads_export'] + $filters, admin_url('admin-post.php')), 'alma_leads_export'),
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
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    private function filters(array $input): array
    {
        return array_filter([
            'search' => sanitize_text_field(wp_unslash((string) ($input['s'] ?? ''))),
            'status' => sanitize_key((string) ($input['status'] ?? '')),
            'form' => sanitize_key((string) ($input['form'] ?? '')),
        ]);
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

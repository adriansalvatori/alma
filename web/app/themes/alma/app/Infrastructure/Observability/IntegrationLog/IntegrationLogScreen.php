<?php

namespace App\Infrastructure\Observability\IntegrationLog;

use Throwable;

/**
 * Tools → Integration log: the last outbound calls, read-only, filterable by host. Bodies are
 * already redacted at write time.
 */
class IntegrationLogScreen
{
    public const SLUG = 'alma-integration-log';

    public function register(): void
    {
        add_action('admin_menu', function () {
            add_management_page(__('Integration log', 'alma'), __('Integration log', 'alma'), 'manage_options', self::SLUG, [$this, 'render']);
        });
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $host = sanitize_text_field(wp_unslash((string) ($_GET['host'] ?? '')));

        try {
            $calls = IntegrationCall::query()
                ->when($host !== '', fn ($query) => $query->where('host', $host))
                ->latest('id')
                ->limit(100)
                ->get();
            $hosts = IntegrationCall::query()->distinct()->orderBy('host')->pluck('host');
        } catch (Throwable) {
            echo '<div class="wrap"><h1>'.esc_html__('Integration log', 'alma').'</h1><p>'.esc_html__('The integration log table is missing. Run wp acorn alma:deploy.', 'alma').'</p></div>';

            return;
        }

        echo '<div class="wrap"><h1>'.esc_html__('Integration log', 'alma').'</h1>';
        echo '<p>'.esc_html__('Outbound HTTP calls made by the site, newest first. Secrets are redacted before they are stored.', 'alma').'</p>';
        echo '<form method="get"><input type="hidden" name="page" value="'.esc_attr(self::SLUG).'"><select name="host"><option value="">'.esc_html__('All hosts', 'alma').'</option>';

        foreach ($hosts as $option) {
            echo '<option value="'.esc_attr($option).'"'.selected($host, $option, false).'>'.esc_html($option).'</option>';
        }

        echo '</select> <button class="button">'.esc_html__('Filter', 'alma').'</button></form>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('When', 'alma').'</th><th>'.esc_html__('Request', 'alma').'</th><th>'.esc_html__('Status', 'alma').'</th><th>'.esc_html__('Time', 'alma').'</th><th>'.esc_html__('Response', 'alma').'</th></tr></thead><tbody>';

        foreach ($calls as $call) {
            $status = $call->error ?? (string) ($call->status ?? '');

            echo '<tr><td>'.esc_html($call->created_at?->toDateTimeString() ?? '').'</td>';
            echo '<td><code>'.esc_html($call->method).'</code> '.esc_html($call->url).'</td>';
            echo '<td>'.($call->succeeded() ? '' : '<strong>').esc_html($status).($call->succeeded() ? '' : '</strong>').'</td>';
            echo '<td>'.esc_html($call->duration_ms !== null ? $call->duration_ms.' ms' : '').'</td>';
            echo '<td><details><summary>'.esc_html__('Body', 'alma').'</summary><pre style="white-space:pre-wrap;max-width:60ch">'.esc_html((string) $call->response_body).'</pre></details></td></tr>';
        }

        echo '</tbody></table></div>';
    }
}

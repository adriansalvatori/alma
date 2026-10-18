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
            $missing = false;
        } catch (Throwable) {
            $calls = collect();
            $hosts = collect();
            $missing = true;
        }

        echo view('admin.integration-log', [
            'calls' => $calls,
            'hosts' => $hosts,
            'host' => $host,
            'missing' => $missing,
            'slug' => self::SLUG,
        ])->render();
    }
}

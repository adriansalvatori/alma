<?php

namespace App\Domains\Seo\Admin;

use App\Domains\Seo\Redirects\Redirect;
use App\Domains\Seo\Redirects\RedirectManager;
use App\Domains\Seo\Redirects\RedirectRejected;
use App\Domains\Seo\Redirects\RedirectStore;
use Throwable;

/**
 * Tools → Redirects: editors add and remove redirects without a deploy. Redirects from
 * config/redirects.php are listed read-only, because changing them is a code change.
 */
class RedirectsScreen
{
    public const SLUG = 'alma-redirects';

    public const CAPABILITY = 'manage_options';

    public function __construct(
        private readonly RedirectManager $manager,
        private readonly RedirectStore $store,
    ) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_alma_redirect_create', [$this, 'create']);
        add_action('admin_post_alma_redirect_delete', [$this, 'delete']);
    }

    public function menu(): void
    {
        add_management_page(__('Redirects', 'alma'), __('Redirects', 'alma'), self::CAPABILITY, self::SLUG, [$this, 'render']);
    }

    public function create(): void
    {
        $this->authorize('alma_redirect_create');

        try {
            $this->manager->create(
                wp_unslash((string) ($_POST['from_path'] ?? '')),
                wp_unslash((string) ($_POST['to_url'] ?? '')),
                (int) ($_POST['status_code'] ?? 301),
                sanitize_text_field(wp_unslash((string) ($_POST['note'] ?? ''))),
                get_current_user_id(),
            );

            $this->back(['created' => 1]);
        } catch (RedirectRejected $e) {
            $this->back(['error' => rawurlencode($e->getMessage())]);
        }
    }

    public function delete(): void
    {
        $this->authorize('alma_redirect_delete');

        $this->manager->delete((int) ($_POST['id'] ?? 0));

        $this->back(['deleted' => 1]);
    }

    public function render(): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            return;
        }

        try {
            $rows = Redirect::query()->orderBy('from_path')->get();
            $missing = false;
        } catch (Throwable) {
            $rows = collect();
            $missing = true;
        }

        echo view('seo::admin.redirects', [
            'rows' => $rows,
            'code' => $this->store->codeMap(),
            'missing' => $missing,
            'notices' => $this->notices(),
        ])->render();
    }

    /**
     * The outcome of the last create/delete, from the query string the redirect back carried.
     *
     * @return list<array{0: string, 1: string}> [type, message] pairs
     */
    private function notices(): array
    {
        $notices = [];

        if (isset($_GET['created'])) {
            $notices[] = ['success', __('Redirect added.', 'alma')];
        }

        if (isset($_GET['deleted'])) {
            $notices[] = ['success', __('Redirect deleted.', 'alma')];
        }

        if (isset($_GET['error'])) {
            $notices[] = ['error', rawurldecode(wp_unslash((string) $_GET['error']))];
        }

        return $notices;
    }

    private function authorize(string $action): void
    {
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to manage redirects.', 'alma'), '', ['response' => 403]);
        }

        check_admin_referer($action);
    }

    /**
     * @param  array<string, int|string>  $args
     */
    private function back(array $args): never
    {
        wp_safe_redirect(add_query_arg($args, admin_url('tools.php?page='.self::SLUG)));
        exit;
    }
}

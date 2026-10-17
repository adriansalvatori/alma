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
        } catch (Throwable) {
            $rows = collect();
            echo '<div class="notice notice-error"><p>'.esc_html__('The redirects table is missing. Run wp acorn alma:deploy.', 'alma').'</p></div>';
        }

        echo '<div class="wrap"><h1>'.esc_html__('Redirects', 'alma').'</h1>';
        $this->notices();

        echo '<h2>'.esc_html__('Add a redirect', 'alma').'</h2>';
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
        wp_nonce_field('alma_redirect_create');
        echo '<input type="hidden" name="action" value="alma_redirect_create">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label for="from_path">'.esc_html__('From path', 'alma').'</label></th><td><input name="from_path" id="from_path" class="regular-text" placeholder="/old-page" required></td></tr>';
        echo '<tr><th><label for="to_url">'.esc_html__('To', 'alma').'</label></th><td><input name="to_url" id="to_url" class="regular-text" placeholder="/new-page/ or https://example.com/" required></td></tr>';
        echo '<tr><th><label for="status_code">'.esc_html__('Type', 'alma').'</label></th><td><select name="status_code" id="status_code"><option value="301">301 '.esc_html__('Permanent', 'alma').'</option><option value="302">302 '.esc_html__('Temporary', 'alma').'</option></select></td></tr>';
        echo '<tr><th><label for="note">'.esc_html__('Note', 'alma').'</label></th><td><input name="note" id="note" class="regular-text" maxlength="255"></td></tr>';
        echo '</tbody></table>';
        submit_button(__('Add redirect', 'alma'));
        echo '</form>';

        echo '<h2>'.esc_html__('Redirects', 'alma').'</h2>';
        echo '<table class="widefat striped"><thead><tr><th>'.esc_html__('From', 'alma').'</th><th>'.esc_html__('To', 'alma').'</th><th>'.esc_html__('Type', 'alma').'</th><th>'.esc_html__('Hits', 'alma').'</th><th>'.esc_html__('Note', 'alma').'</th><th></th></tr></thead><tbody>';

        foreach ($rows as $row) {
            echo '<tr><td><code>'.esc_html($row->from_path).'</code></td><td>'.esc_html($row->to_url).'</td><td>'.esc_html((string) $row->status_code).'</td><td>'.esc_html((string) $row->hits).'</td><td>'.esc_html((string) $row->note).'</td><td>';
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" onsubmit="return confirm(\''.esc_js(__('Delete this redirect?', 'alma')).'\')">';
            wp_nonce_field('alma_redirect_delete');
            echo '<input type="hidden" name="action" value="alma_redirect_delete"><input type="hidden" name="id" value="'.esc_attr((string) $row->id).'">';
            echo '<button class="button-link-delete">'.esc_html__('Delete', 'alma').'</button></form></td></tr>';
        }

        foreach ($this->store->codeMap() as $path => $redirect) {
            echo '<tr><td><code>'.esc_html($path).'</code></td><td>'.esc_html($redirect['to']).'</td><td>'.esc_html((string) $redirect['status']).'</td><td>&ndash;</td><td><em>'.esc_html__('In code (config/redirects.php)', 'alma').'</em></td><td></td></tr>';
        }

        echo '</tbody></table></div>';
    }

    private function notices(): void
    {
        if (isset($_GET['created'])) {
            echo '<div class="notice notice-success is-dismissible"><p>'.esc_html__('Redirect added.', 'alma').'</p></div>';
        }

        if (isset($_GET['deleted'])) {
            echo '<div class="notice notice-success is-dismissible"><p>'.esc_html__('Redirect deleted.', 'alma').'</p></div>';
        }

        if (isset($_GET['error'])) {
            echo '<div class="notice notice-error"><p>'.esc_html(rawurldecode(wp_unslash((string) $_GET['error']))).'</p></div>';
        }
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

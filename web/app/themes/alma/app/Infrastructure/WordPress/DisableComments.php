<?php

namespace App\Infrastructure\WordPress;

/**
 * Switch comments and pingbacks off everywhere: front end, wp-admin, the toolbar, REST and feeds.
 *
 * Marketing sites don't take comments, and an open comment form is a spam inlet. So this is the
 * default (COMMENTS_ENABLED=false). Closing comments in Settings → Discussion only affects new
 * posts, which leaves every existing post open; this closes them all at render time instead.
 * Comments already in the database are hidden, not deleted, so switching back loses nothing.
 */
class DisableComments
{
    public function register(): void
    {
        if (config('alma.comments')) {
            return;
        }

        // After every post type is registered, including those added late by plugins.
        add_action('init', [$this, 'removePostTypeSupport'], 100);

        add_filter('comments_open', '__return_false', 20);
        add_filter('pings_open', '__return_false', 20);
        add_filter('comments_array', '__return_empty_array', 20);
        add_filter('get_comments_number', '__return_zero', 20);
        add_filter('feed_links_show_comments_feed', '__return_false');
        add_filter('rest_endpoints', [$this, 'removeRestEndpoints']);

        add_action('admin_menu', [$this, 'removeAdminScreens'], 999);
        add_action('admin_init', [$this, 'redirectAdminScreens']);
        add_action('wp_dashboard_setup', [$this, 'removeDashboardWidget'], 999);
        add_action('admin_bar_menu', [$this, 'removeToolbarNode'], 999);
    }

    public function removePostTypeSupport(): void
    {
        foreach (get_post_types() as $type) {
            remove_post_type_support($type, 'comments');
            remove_post_type_support($type, 'trackbacks');
        }
    }

    /**
     * Without this, anyone can still POST a comment to /wp-json/wp/v2/comments.
     *
     * @param  array<string, mixed>  $endpoints
     * @return array<string, mixed>
     */
    public function removeRestEndpoints(array $endpoints): array
    {
        foreach (array_keys($endpoints) as $route) {
            if (str_starts_with($route, '/wp/v2/comments')) {
                unset($endpoints[$route]);
            }
        }

        return $endpoints;
    }

    public function removeAdminScreens(): void
    {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
    }

    /**
     * The menu entries are gone, but the screens still answer their URLs.
     */
    public function redirectAdminScreens(): void
    {
        global $pagenow;

        if (in_array($pagenow, ['edit-comments.php', 'comment.php', 'options-discussion.php'], true)) {
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    public function removeDashboardWidget(): void
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    public function removeToolbarNode(object $toolbar): void
    {
        $toolbar->remove_node('comments');
    }
}

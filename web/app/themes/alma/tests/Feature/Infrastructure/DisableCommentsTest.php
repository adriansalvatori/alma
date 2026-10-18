<?php

use App\Infrastructure\WordPress\DisableComments;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $GLOBALS['_wp_post_type_features'] = ['post' => ['comments' => true, 'trackbacks' => true], 'page' => ['comments' => true]];
});

it('is on by default and closes comments and pingbacks everywhere', function () {
    expect(config('alma.comments'))->toBeFalse()
        ->and(comments_open(1))->toBeFalse()
        ->and(apply_filters('pings_open', true, 1))->toBeFalse()
        ->and(apply_filters('comments_array', [new stdClass], 1))->toBe([])
        ->and(get_comments_number(1))->toBe(0)
        ->and(apply_filters('feed_links_show_comments_feed', true))->toBeFalse();
});

it('removes comment support from every post type once they are registered', function () {
    do_action('init');

    expect(post_type_supports('post', 'comments'))->toBeFalse()
        ->and(post_type_supports('post', 'trackbacks'))->toBeFalse()
        ->and(post_type_supports('page', 'comments'))->toBeFalse();
});

it('closes the REST endpoints that accept comments', function () {
    $endpoints = apply_filters('rest_endpoints', [
        '/wp/v2/comments' => [],
        '/wp/v2/comments/(?P<id>[\d]+)' => [],
        '/wp/v2/posts' => [],
    ]);

    expect(array_keys($endpoints))->toBe(['/wp/v2/posts']);
});

it('removes the admin screens, dashboard widget and toolbar entry', function () {
    // Called directly: firing admin_menu would run every module's screens too.
    $hook = new DisableComments;
    $hook->removeAdminScreens();
    $hook->removeDashboardWidget();

    $toolbar = new class
    {
        public array $removed = [];

        public function remove_node(string $id): void
        {
            $this->removed[] = $id;
        }
    };
    $hook->removeToolbarNode($toolbar);

    expect(WordPress::$options['__removed_menus'])->toContain('edit-comments.php', 'options-general.php > options-discussion.php')
        ->and(WordPress::$options['__removed_meta_boxes'])->toContain('dashboard_recent_comments')
        ->and($toolbar->removed)->toBe(['comments']);
});

it('leaves comments alone when COMMENTS_ENABLED is true', function () {
    WordPress::$filters = [];
    config(['alma.comments' => true]);

    (new DisableComments)->register();

    expect(comments_open(1))->toBeTrue()
        ->and(has_filter('rest_endpoints'))->toBeFalse();
});

<?php

namespace App\Domains\Content\Patterns;

use App\Support\Pattern;
use stdClass;
use WP_Post;
use WP_REST_Request;

/**
 * Keeps a pattern page's content in git when someone edits the page in the block editor.
 *
 * A pattern page's post_content is one line, `<!-- wp:pattern {"slug":"alma/home"} /-->`, and
 * its markup lives in patterns/home.php. The block editor does not keep that line: core/pattern
 * expands into its blocks the moment the page opens, and the next save writes those blocks back.
 * Remote Leverage measured it: saving an unrelated field replaced the reference with 835 bytes of
 * expanded markup, and from then on fixes to the pattern never reached the page.
 *
 * Until the copy-overlay module is ported (copy edits stored per page on top of the pattern),
 * pattern pages are code-owned:
 *
 * - On save through REST (the editor's path), post_content never replaces a bare reference.
 *   Title, slug, featured image and SEO fields save normally.
 * - The editor shows a notice naming the pattern file, with a "Detach from pattern" action. A
 *   detach sends DETACH_PARAM with the save; the page keeps its expanded blocks and becomes
 *   database content, recorded in DETACHED_META so it can be promoted back into git later.
 *
 * Server-side writers (wp_update_post from CLI, sync, MCP abilities) are not guarded: they may
 * expand a page on purpose.
 */
class PatternPageEditing
{
    /** Request-only parameter the editor adds to the save when someone detaches the page. */
    public const DETACH_PARAM = 'alma_detach_from_pattern';

    /** Where a detached page came from: pattern slug, time and user. */
    public const DETACHED_META = '_alma_detached_from_pattern';

    /**
     * Pages detached during this request, post ID => pattern slug, recorded after the insert so
     * a save that fails records nothing.
     *
     * @var array<int, string>
     */
    private static array $detached = [];

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'guardRestSaves']);
        add_action('admin_head', [$this, 'printEditorData'], 1);
    }

    public function guardRestSaves(): void
    {
        foreach (get_post_types(['show_in_rest' => true]) as $type) {
            add_filter("rest_pre_insert_{$type}", [self::class, 'keepPatternReference'], 10, 2);
            add_action("rest_after_insert_{$type}", [self::class, 'recordDetach'], 10, 1);
        }
    }

    /**
     * rest_pre_insert_{$type}: keep a pattern page a pattern reference unless it is detached.
     *
     * Unsetting post_content (rather than overwriting it) lets wp_update_post() keep the stored
     * value, which is the reference itself.
     *
     * @param  stdClass|mixed  $prepared
     * @param  WP_REST_Request|mixed  $request
     * @return stdClass|mixed
     */
    public static function keepPatternReference($prepared, $request = null)
    {
        if (! $prepared instanceof stdClass || empty($prepared->ID) || ! property_exists($prepared, 'post_content')) {
            return $prepared;
        }

        $id = (int) $prepared->ID;
        $stored = (string) get_post_field('post_content', $id);
        $slug = Pattern::referencedSlug($stored);

        if ($slug === null || (string) $prepared->post_content === $stored) {
            return $prepared;
        }

        if (self::detachRequested($request)) {
            self::$detached[$id] = $slug;

            return $prepared;
        }

        unset($prepared->post_content);

        return $prepared;
    }

    /**
     * rest_after_insert_{$type}: record a detach that went through.
     */
    public static function recordDetach(mixed $post): void
    {
        if (! $post instanceof WP_Post || ! isset(self::$detached[$post->ID])) {
            return;
        }

        update_post_meta($post->ID, self::DETACHED_META, [
            'pattern' => self::$detached[$post->ID],
            'at' => time(),
            'user' => get_current_user_id(),
        ]);

        unset(self::$detached[$post->ID]);
    }

    /**
     * Tell the editor script whether this is a code-owned pattern page, and which file owns it.
     */
    public function printEditorData(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (! $screen || ! method_exists($screen, 'is_block_editor') || ! $screen->is_block_editor()) {
            return;
        }

        $post = get_post();
        $slug = $post ? Pattern::referencedSlug((string) $post->post_content) : null;

        $data = $slug === null ? null : [
            'postId' => $post->ID,
            'slug' => $slug,
            'file' => 'patterns/'.self::fileFor($slug),
            'param' => self::DETACH_PARAM,
        ];

        echo '<script>window.almaPatternPage = '.wp_json_encode($data).';</script>';
    }

    /**
     * The pattern file that registers $slug ("alma/home" → "home.php"), by header, falling back
     * to the slug's last segment.
     */
    public static function fileFor(string $slug): string
    {
        foreach (glob(get_theme_file_path('patterns').'/*.php') ?: [] as $file) {
            if (trim((string) (get_file_data($file, ['slug' => 'Slug'])['slug'] ?? '')) === $slug) {
                return basename($file);
            }
        }

        return basename($slug).'.php';
    }

    private static function detachRequested(mixed $request): bool
    {
        if (! is_object($request) || ! method_exists($request, 'get_param')) {
            return false;
        }

        return filter_var($request->get_param(self::DETACH_PARAM), FILTER_VALIDATE_BOOLEAN);
    }
}

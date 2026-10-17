<?php

namespace App\Domains\Content\Deploy;

use App\Infrastructure\Deploy\DeployTask;
use App\Support\Pattern;

/**
 * Create the pattern pages listed in config/content.php that do not exist yet.
 *
 * A page whose slug already exists is never touched: an editor may have detached it, renamed it
 * or deliberately replaced its content. The front page is set only when the site has none, so a
 * fresh install gets a homepage and an existing site keeps its own.
 *
 * Guarded by a hash of the config, so a deploy that changed nothing here costs one option read.
 */
class EnsurePatternPages implements DeployTask
{
    public const HASH_OPTION = 'alma_pattern_pages_hash';

    public function label(): string
    {
        return 'Create missing pattern pages';
    }

    public function priority(): int
    {
        return 50;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        $pages = (array) config('content.pages', []);
        $hash = sha1((string) json_encode($pages));

        if (get_option(self::HASH_OPTION) === $hash && $this->allExist($pages)) {
            return;
        }

        foreach ($pages as $slug => $page) {
            $id = $this->ensure((string) $slug, (array) $page);

            if ($id && ! empty($page['front_page']) && ! (int) get_option('page_on_front')) {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $id);
            }
        }

        update_option(self::HASH_OPTION, $hash);
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private function ensure(string $slug, array $page): ?int
    {
        if ($existing = get_page_by_path($slug)) {
            return $existing->ID;
        }

        $id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_name' => $slug,
            'post_title' => $page['title'] ?? ucfirst($slug),
            'post_content' => wp_slash(Pattern::reference((string) $page['pattern'])),
        ], true);

        return is_wp_error($id) ? null : (int) $id;
    }

    /**
     * @param  array<string, mixed>  $pages
     */
    private function allExist(array $pages): bool
    {
        foreach (array_keys($pages) as $slug) {
            if (! get_page_by_path((string) $slug)) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace App\Domains\Ai\Abilities\Pages;

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Ai\Support\BlockInspector;
use WP_Error;
use WP_Post;

/**
 * One page, as an agent needs to see it before proposing a change: whether its layout is owned by
 * a pattern file in git (edits belong in that file, or need a detach), and which blocks it uses.
 */
class DescribePage extends Ability
{
    /** Where the Content module records a page that left its pattern. */
    private const DETACHED_META = '_alma_detached_from_pattern';

    public function __construct(private readonly BlockInspector $blocks) {}

    public function name(): string
    {
        return 'alma/describe-page';
    }

    public function label(): string
    {
        return __('Describe page', 'alma');
    }

    public function description(): string
    {
        return __('Describe one page by id or slug: status, URL, whether it is a pattern page managed in code (patterns/<slug>.php) or detached database content, and the blocks it is built from.', 'alma');
    }

    public function category(): string
    {
        return 'alma-content';
    }

    public function readOnly(): bool
    {
        return true;
    }

    public function idempotent(): bool
    {
        return true;
    }

    protected function inputProperties(): array
    {
        return [
            'id' => ['type' => 'integer', 'minimum' => 1],
            'slug' => ['type' => 'string'],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_pages');
    }

    public function execute(array $input): array|WP_Error
    {
        $page = $this->find($input);

        if (! $page) {
            return new WP_Error('alma_ai_page_not_found', __('No page with that id or slug.', 'alma'));
        }

        $slug = $this->blocks->patternSlug($page->post_content);
        $content = $slug !== null ? $this->blocks->patternContent($slug) : $page->post_content;
        $detached = get_post_meta($page->ID, self::DETACHED_META, true);

        return [
            'id' => $page->ID,
            'title' => $page->post_title,
            'slug' => $page->post_name,
            'status' => $page->post_status,
            'url' => (string) get_permalink($page),
            'modified' => $page->post_modified,
            'pattern' => $slug === null ? null : [
                'slug' => $slug,
                'file' => 'patterns/'.(str_contains($slug, '/') ? substr($slug, strpos($slug, '/') + 1) : $slug).'.php',
                'registered' => $content !== '',
            ],
            'code_owned' => $slug !== null,
            'detached' => $detached !== '' && $detached !== false ? $detached : null,
            'blocks' => array_map(
                fn (string $name, int $count) => ['name' => $name, 'count' => $count],
                array_keys($counts = $this->blocks->blockCounts($content)),
                $counts,
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function find(array $input): ?WP_Post
    {
        if (! empty($input['id'])) {
            $page = get_post((int) $input['id']);

            return $page instanceof WP_Post && $page->post_type === 'page' ? $page : null;
        }

        if (is_string($input['slug'] ?? null) && $input['slug'] !== '') {
            return get_page_by_path(trim($input['slug'], '/'), OBJECT, 'page');
        }

        return null;
    }
}

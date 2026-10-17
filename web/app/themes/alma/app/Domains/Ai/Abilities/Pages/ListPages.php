<?php

namespace App\Domains\Ai\Abilities\Pages;

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Ai\Support\BlockInspector;
use WP_Error;
use WP_Post;

class ListPages extends Ability
{
    public function __construct(private readonly BlockInspector $blocks) {}

    public function name(): string
    {
        return 'alma/list-pages';
    }

    public function label(): string
    {
        return __('List pages', 'alma');
    }

    public function description(): string
    {
        return __('List the site\'s pages with their status, URL, and the pattern file a page is built from (pattern pages are managed in code). Use describe-page for one page\'s blocks.', 'alma');
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
            'search' => ['type' => 'string', 'description' => 'Match against the title and content.'],
            'status' => ['type' => 'string', 'enum' => ['publish', 'draft', 'pending', 'private', 'any'], 'default' => 'any'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 50],
        ];
    }

    public function outputSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'url' => ['type' => 'string'],
                    'pattern' => ['type' => ['string', 'null']],
                    'modified' => ['type' => 'string'],
                ],
            ],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_pages');
    }

    public function execute(array $input): array
    {
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => $input['status'] ?? 'any',
            's' => is_string($input['search'] ?? null) ? $input['search'] : '',
            'numberposts' => min(100, max(1, (int) ($input['limit'] ?? 50))),
            'orderby' => 'title',
            'order' => 'ASC',
            'suppress_filters' => false,
        ]);

        return array_map(fn (WP_Post $page) => [
            'id' => $page->ID,
            'title' => $page->post_title,
            'slug' => $page->post_name,
            'status' => $page->post_status,
            'url' => (string) get_permalink($page),
            'pattern' => $this->blocks->patternSlug($page->post_content),
            'modified' => $page->post_modified,
        ], $pages);
    }
}

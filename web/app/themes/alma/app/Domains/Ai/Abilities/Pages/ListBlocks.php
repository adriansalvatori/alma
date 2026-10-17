<?php

namespace App\Domains\Ai\Abilities\Pages;

use App\Domains\Ai\Abilities\Ability;
use WP_Block_Type_Registry;
use WP_Error;

/**
 * The theme's ACF blocks and their fields: the vocabulary an agent composes pages from. Reusing
 * an existing block is always preferred over new markup (docs/blocks.md).
 */
class ListBlocks extends Ability
{
    public function name(): string
    {
        return 'alma/list-blocks';
    }

    public function label(): string
    {
        return __('List blocks', 'alma');
    }

    public function description(): string
    {
        return __('List the site\'s custom ACF blocks (acf/*) with their descriptions and editable fields. Prefer these blocks over new markup when building or editing a page.', 'alma');
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

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_pages');
    }

    public function execute(array $input): array
    {
        $blocks = [];

        foreach (WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $type) {
            if (! str_starts_with((string) $name, 'acf/')) {
                continue;
            }

            $blocks[] = [
                'name' => (string) $name,
                'title' => (string) $type->title,
                'description' => (string) $type->description,
                'fields' => $this->fields((string) $name),
            ];
        }

        usort($blocks, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return $blocks;
    }

    /**
     * @return list<array{name: string, label: string, type: string}>
     */
    private function fields(string $block): array
    {
        if (! function_exists('acf_get_field_groups') || ! function_exists('acf_get_fields')) {
            return [];
        }

        $fields = [];

        foreach ((array) acf_get_field_groups(['block' => $block]) as $group) {
            foreach ((array) acf_get_fields($group) as $field) {
                $fields[] = [
                    'name' => (string) ($field['name'] ?? ''),
                    'label' => (string) ($field['label'] ?? ''),
                    'type' => (string) ($field['type'] ?? ''),
                ];
            }
        }

        return $fields;
    }
}

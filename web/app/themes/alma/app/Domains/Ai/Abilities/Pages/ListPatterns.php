<?php

namespace App\Domains\Ai\Abilities\Pages;

use App\Domains\Ai\Abilities\Ability;
use WP_Block_Patterns_Registry;
use WP_Error;

class ListPatterns extends Ability
{
    /** The theme's own patterns; core and plugin patterns are noise for page building. */
    private const THEME_PREFIX = 'alma/';

    public function name(): string
    {
        return 'alma/list-patterns';
    }

    public function label(): string
    {
        return __('List patterns', 'alma');
    }

    public function description(): string
    {
        return __('List the theme\'s block patterns: whole-page patterns (a page that references one is managed in code) and reusable sections. Pass all=true to include core and plugin patterns.', 'alma');
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
            'all' => ['type' => 'boolean', 'default' => false],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_pages');
    }

    public function execute(array $input): array
    {
        $all = (bool) ($input['all'] ?? false);
        $patterns = [];

        foreach (WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern) {
            $name = (string) ($pattern['name'] ?? '');

            if (! $all && ! str_starts_with($name, self::THEME_PREFIX)) {
                continue;
            }

            $categories = array_values((array) ($pattern['categories'] ?? []));

            $patterns[] = [
                'name' => $name,
                'title' => (string) ($pattern['title'] ?? ''),
                'description' => (string) ($pattern['description'] ?? ''),
                'categories' => $categories,
                'page' => in_array('alma-pages', $categories, true),
            ];
        }

        usort($patterns, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return $patterns;
    }
}

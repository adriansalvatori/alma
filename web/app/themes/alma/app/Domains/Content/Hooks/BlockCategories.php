<?php

namespace App\Domains\Content\Hooks;

/**
 * The "Alma" category the theme's blocks are filed under, listed first in the inserter.
 */
class BlockCategories
{
    public function register(): void
    {
        add_filter('block_categories_all', [$this, 'categories']);
    }

    /**
     * @param  list<array<string, mixed>>  $categories
     * @return list<array<string, mixed>>
     */
    public function categories(array $categories): array
    {
        return [
            ['slug' => 'alma', 'title' => __('Alma', 'alma'), 'icon' => null],
            ...array_values(array_filter($categories, fn (array $category) => ($category['slug'] ?? '') !== 'alma')),
        ];
    }
}

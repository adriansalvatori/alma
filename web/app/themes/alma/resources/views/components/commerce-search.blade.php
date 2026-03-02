<?php

use Livewire\Component;

new class extends Component {
    public string $searchQuery = '';
    public array $results = ['products' => [], 'categories' => [], 'posts' => []];

    public function updatedSearchQuery()
    {
        $this->results = ['products' => [], 'categories' => [], 'posts' => []];

        if (strlen($this->searchQuery) < 2) {
            return;
        }

        // 1. Search Products
        $productQuery = new \WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 4,
            's' => $this->searchQuery,
        ]);

        if ($productQuery->have_posts()) {
            foreach ($productQuery->posts as $post) {
                $_product = wc_get_product($post->ID);
                if ($_product) {
                    $this->results['products'][] = [
                        'id' => $_product->get_id(),
                        'name' => $_product->get_name(),
                        'price' => $_product->get_price_html(),
                        'permalink' => $_product->get_permalink(),
                        'thumbnail' => $_product->get_image(),
                    ];
                }
            }
        }

        // 2. Search Posts
        $postQuery = new \WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            's' => $this->searchQuery,
        ]);

        if ($postQuery->have_posts()) {
            foreach ($postQuery->posts as $post) {
                $this->results['posts'][] = [
                    'id' => $post->ID,
                    'name' => get_the_title($post->ID),
                    'permalink' => get_permalink($post->ID),
                ];
            }
        }

        // 3. Search Categories
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'search' => $this->searchQuery,
            'hide_empty' => false,
            'number' => 4,
        ]);

        if (!is_wp_error($categories) && !empty($categories)) {
            foreach ($categories as $category) {
                $this->results['categories'][] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'permalink' => get_term_link($category),
                    'count' => $category->count,
                ];
            }
        }
    }

    public function getHasResultsProperty()
    {
        return !empty($this->results['products']) || !empty($this->results['categories']) || !empty($this->results['posts']);
    }
};
?>

<div class="contents" x-data @keydown.window.prevent.cmd.k="$el.querySelector('button').click()"
    @keydown.window.prevent.ctrl.k="$el.querySelector('button').click()">
    <flux:tooltip content="Search (⌘K)" position="top">
        <flux:modal.trigger name="commerce-search">
            <flux:button variant="subtle" aria-label="Search" class="px-2!">
                <flux:icon.magnifying-glass
                    class="w-5 h-5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" />
            </flux:button>
        </flux:modal.trigger>
    </flux:tooltip>

    <flux:modal name="commerce-search" class="w-full max-w-2xl p-0!" variant="bare">
        <div
            class="flex flex-col h-[70vh] max-h-[700px] bg-white dark:bg-zinc-900 rounded-xl overflow-hidden shadow-2xl border border-zinc-200 dark:border-zinc-800 text-left">
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center">
                <flux:input wire:model.live.debounce.300ms="searchQuery" icon="magnifying-glass"
                    placeholder="Search products, posts, categories..."
                    class="w-full border-none shadow-none focus-within:ring-0 text-lg!" />
                <flux:modal.close>
                    <flux:button variant="subtle" icon="x-mark" class="px-2!" />
                </flux:modal.close>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                @if (strlen($searchQuery) > 0)
                    @if (!$this->hasResults)
                        <div class="text-sm text-zinc-500 text-center mt-8">No results found for "{{ $searchQuery }}"
                        </div>
                    @else
                        <div class="space-y-6">
                            {{-- Categories --}}
                            @if (!empty($this->results['categories']))
                                <div>
                                    <h3 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2 px-2">
                                        Categories</h3>
                                    <div class="space-y-1">
                                        @foreach ($this->results['categories'] as $cat)
                                            <a href="{{ $cat['permalink'] }}"
                                                class="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors group">
                                                <div class="flex items-center gap-3">
                                                    <flux:icon.folder
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-primary" />
                                                    <span
                                                        class="text-sm font-medium text-zinc-900 dark:text-white group-hover:text-primary">{{ $cat['name'] }}</span>
                                                </div>
                                                <span
                                                    class="text-xs text-zinc-500 bg-zinc-200 dark:bg-zinc-800 px-2 py-0.5 rounded-full">{{ $cat['count'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Products --}}
                            @if (!empty($this->results['products']))
                                <div>
                                    <h3 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2 px-2">
                                        Products</h3>
                                    <div class="space-y-2">
                                        @foreach ($this->results['products'] as $product)
                                            <a href="{{ $product['permalink'] }}"
                                                class="flex items-center gap-4 p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors group">
                                                <div class="w-12 h-12 rounded bg-zinc-100 shrink-0">
                                                    {!! $product['thumbnail'] !!}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div
                                                        class="text-sm font-medium text-zinc-900 dark:text-white truncate group-hover:text-primary">
                                                        {{ $product['name'] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {!! $product['price'] !!}
                                                    </div>
                                                </div>
                                                <flux:icon.chevron-right
                                                    class="w-5 h-5 text-zinc-300 dark:text-zinc-600 group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity" />
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Posts --}}
                            @if (!empty($this->results['posts']))
                                <div>
                                    <h3 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2 px-2">
                                        Articles</h3>
                                    <div class="space-y-1">
                                        @foreach ($this->results['posts'] as $post)
                                            <a href="{{ $post['permalink'] }}"
                                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors group">
                                                <flux:icon.document-text
                                                    class="w-5 h-5 text-zinc-400 shrink-0 group-hover:text-primary" />
                                                <span
                                                    class="text-sm font-medium text-zinc-900 dark:text-white truncate group-hover:text-primary">{{ $post['name'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="text-sm text-zinc-500 text-center mt-8">Start typing to search the store.</div>
                @endif
            </div>
        </div>
    </flux:modal>
</div>

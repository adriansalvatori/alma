<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\FavoritesService;

new class extends Component {
    #[On('favorites-updated')]
    public function renderFavorites()
    {
        // Force re-render when event is received
    }

    public function removeFavorite($productId)
    {
        FavoritesService::removeFavorite($productId);
        $this->dispatch('favorites-updated');
    }

    public function getFavoritesDataProperty()
    {
        $favorites = FavoritesService::getFavorites();
        $items = [];

        if (class_exists('WooCommerce') && !empty($favorites)) {
            foreach ($favorites as $id) {
                $_product = wc_get_product($id);
                if ($_product && $_product->exists() && $_product->is_visible()) {
                    $items[] = [
                        'id' => $_product->get_id(),
                        'name' => $_product->get_name(),
                        'thumbnail' => $_product->get_image(),
                        'price' => $_product->get_price_html(),
                        'permalink' => $_product->get_permalink(),
                    ];
                }
            }
        }

        return $items;
    }
};
?>

<div class="contents">
    <flux:modal.trigger name="favorites-flyout">
        <flux:button variant="subtle" aria-label="Favorites" class="px-2! relative">
            <flux:icon.heart
                class="w-5 h-5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" />
            @if (count($this->favoritesData) > 0)
                <div
                    class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-zinc-900">
                    {{ count($this->favoritesData) }}
                </div>
            @endif
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="favorites-flyout" flyout variant="floating" class="w-full sm:w-96!">
        <div class="flex flex-col h-dvh">
            <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Favorites</h2>
            </div>

            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
                @if (empty($this->favoritesData))
                    <div class="flex flex-col items-center justify-center h-full text-zinc-500 dark:text-zinc-400">
                        <flux:icon.heart class="w-12 h-12 mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p>No favorites yet.</p>
                    </div>
                @else
                    @foreach ($this->favoritesData as $item)
                        <div class="flex items-center gap-4 py-2" wire:key="fav-item-{{ $item['id'] }}">
                            <div class="w-16 h-16 rounded-md overflow-hidden bg-zinc-100 flex-shrink-0">
                                {!! $item['thumbnail'] !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $item['permalink'] }}"
                                    class="text-sm font-medium text-zinc-900 dark:text-white hover:text-primary truncate block">
                                    {{ $item['name'] }}
                                </a>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                    {!! $item['price'] !!}
                                </div>
                            </div>
                            <flux:button variant="subtle" icon="trash"
                                class="px-2! text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950"
                                wire:click="removeFavorite({{ $item['id'] }})" />
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </flux:modal>
</div>

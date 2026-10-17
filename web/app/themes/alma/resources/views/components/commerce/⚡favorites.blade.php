<?php

use App\Domains\Commerce\Services\FavoritesService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    #[On('favorites-updated')]
    public function refresh(): void
    {
        unset($this->favoritesData);
    }

    public function removeFavorite(int $productId, FavoritesService $favorites): void
    {
        $favorites->remove($productId);
        unset($this->favoritesData);

        $this->dispatch('favorites-updated');
    }

    /**
     * Only products that still exist and are visible: a favorite can outlive its product.
     *
     * @return list<array{id: int, name: string, thumbnail: string, price: string, permalink: string}>
     */
    #[Computed]
    public function favoritesData(): array
    {
        if (! function_exists('wc_get_product')) {
            return [];
        }

        $items = [];

        foreach (app(FavoritesService::class)->all() as $id) {
            $product = wc_get_product($id);

            if ($product && $product->exists() && $product->is_visible()) {
                $items[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'thumbnail' => $product->get_image(),
                    'price' => $product->get_price_html(),
                    'permalink' => $product->get_permalink(),
                ];
            }
        }

        return $items;
    }
};
?>

<div class="contents">
    <flux:modal.trigger name="favorites-flyout">
        <flux:button variant="subtle" aria-label="{{ __('Favorites', 'alma') }}" class="px-2! relative">
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
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Favorites', 'alma') }}</h2>
            </div>

            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
                @if (empty($this->favoritesData))
                    <div class="flex flex-col items-center justify-center h-full text-zinc-500 dark:text-zinc-400">
                        <flux:icon.heart class="w-12 h-12 mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p>{{ __('No favorites yet.', 'alma') }}</p>
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
                                wire:click="removeFavorite({{ (int) $item['id'] }})"
                                aria-label="{{ __('Remove from favorites', 'alma') }}" />
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </flux:modal>
</div>

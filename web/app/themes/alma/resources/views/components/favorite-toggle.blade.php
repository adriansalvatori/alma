<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\FavoritesService;

new class extends Component {
    public int $productId;

    public function mount(int $productId)
    {
        $this->productId = $productId;
    }

    #[On('favorites-updated')]
    public function renderFavorites()
    {
        // Re-evaluate boolean state when global event fires
    }

    public function toggle()
    {
        FavoritesService::toggleFavorite($this->productId);
        $this->dispatch('favorites-updated');
    }

    public function getIsFavoritedProperty()
    {
        return FavoritesService::isFavorite($this->productId);
    }
};
?>

<div>
    <button wire:click.prevent="toggle"
        class="absolute top-3 right-3 p-2 rounded-full backdrop-blur-md transition-colors @if ($this->isFavorited) bg-red-50 dark:bg-red-500/20 text-red-500 @else bg-white/70 dark:bg-zinc-900/50 text-zinc-400 hover:text-red-500 @endif shadow-sm z-10">
        <flux:icon.heart variant="{{ $this->isFavorited ? 'solid' : 'outline' }}" class="w-5 h-5" />
    </button>
</div>

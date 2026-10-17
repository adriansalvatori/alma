<?php

use App\Domains\Commerce\Services\FavoritesService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    // Set by the template, never by the browser: a client could otherwise favorite any ID.
    #[Locked]
    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function toggle(FavoritesService $favorites): void
    {
        $favorites->toggle($this->productId);
        unset($this->isFavorited);

        $this->dispatch('favorites-updated');
    }

    #[On('favorites-updated')]
    public function refresh(): void
    {
        unset($this->isFavorited);
    }

    #[Computed]
    public function isFavorited(): bool
    {
        return app(FavoritesService::class)->has($this->productId);
    }
};
?>

<div>
    <button type="button" wire:click.prevent="toggle"
        aria-pressed="{{ $this->isFavorited ? 'true' : 'false' }}"
        aria-label="{{ $this->isFavorited ? __('Remove from favorites', 'alma') : __('Add to favorites', 'alma') }}"
        @class([
            'absolute top-3 right-3 z-10 rounded-full p-2 shadow-sm backdrop-blur-md transition-colors',
            'bg-red-50 text-red-500 dark:bg-red-500/20' => $this->isFavorited,
            'bg-white/70 text-zinc-400 hover:text-red-500 dark:bg-zinc-900/50' => ! $this->isFavorited,
        ])>
        <flux:icon.heart :variant="$this->isFavorited ? 'solid' : 'outline'" class="h-5 w-5" />
    </button>
</div>

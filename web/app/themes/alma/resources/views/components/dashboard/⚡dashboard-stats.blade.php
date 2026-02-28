<?php

use Livewire\Component;
use App\Dashboard\WidgetManager;

new class extends Component {
    public function with(WidgetManager $manager): array
    {
        return [
            'widgets' => $manager->getWidgets(),
        ];
    }
};
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($widgets as $widget)
        <div
            class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ $widget->title() }}</h3>
                <div class="text-zinc-600 dark:text-zinc-400">
                    {!! $widget->render() !!}
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 text-zinc-500">
            {{ __('No widgets added yet.', 'alma') }}
        </div>
    @endforelse
</div>
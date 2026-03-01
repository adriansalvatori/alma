<div class="{{ $block->classes }} py-16 bg-zinc-50 dark:bg-zinc-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                {{ $sectionTitle }}
            </h2>
            <div class="mt-4 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Feature 1 (Full Width Top row in wireframe, or takes up half the grid in 2x2. Let's make it span 2 cols on md) --}}
            <div
                class="md:col-span-2 bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col md:flex-row">
                <div class="p-8 md:w-1/2 flex flex-col justify-center">
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mb-4">
                        {{ $feature1Title }}
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400">
                        {{ $feature1Desc }}
                    </p>
                </div>
                <div class="bg-zinc-100 dark:bg-zinc-700 md:w-1/2 h-64 md:h-auto flex items-center justify-center">
                    <flux:icon.photo class="w-16 h-16 text-zinc-300 dark:text-zinc-500" />
                </div>
            </div>

            {{-- Feature 2 --}}
            <div
                class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col">
                <div class="p-8 flex-1">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">
                        {{ $feature2Title }}
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        {{ $feature2Desc }}
                    </p>
                </div>
                <div class="bg-zinc-100 dark:bg-zinc-700 h-48 flex items-center justify-center">
                    <flux:icon.chart-bar class="w-12 h-12 text-zinc-300 dark:text-zinc-500" />
                </div>
            </div>

            {{-- Feature 3 --}}
            <div
                class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden flex flex-col">
                <div class="p-8 flex-1">
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-3">
                        {{ $feature3Title }}
                    </h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">
                        {{ $feature3Desc }}
                    </p>
                </div>
                <div class="bg-zinc-100 dark:bg-zinc-700 h-48 flex items-center justify-center">
                    <flux:icon.bolt class="w-12 h-12 text-zinc-300 dark:text-zinc-500" />
                </div>
            </div>

        </div>
    </div>
</div>

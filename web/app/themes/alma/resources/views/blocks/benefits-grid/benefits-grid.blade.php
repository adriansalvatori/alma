<section class="py-16 {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                {{ $attributes['sectionTitle'] ?? 'Why Choose Us Section' }}
            </h2>
            <div class="mt-4 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 text-center">

            {{-- Benefit 1 --}}
            <div class="flex flex-col items-center">
                <div
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 mb-6">
                    <flux:icon.rocket-launch class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Blazing Fast</h3>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Use icons, brief titles, and benefit-led text to explain why users should pick your app.
                </p>
            </div>

            {{-- Benefit 2 --}}
            <div class="flex flex-col items-center">
                <div
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 mb-6">
                    <flux:icon.shield-check class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Secure</h3>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Use icons, brief titles, and benefit-led text to explain why users should pick your app.
                </p>
            </div>

            {{-- Benefit 3 --}}
            <div class="flex flex-col items-center">
                <div
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 mb-6">
                    <flux:icon.device-phone-mobile class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Mobile First</h3>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Use icons, brief titles, and benefit-led text to explain why users should pick your app.
                </p>
            </div>

            {{-- Benefit 4 --}}
            <div class="flex flex-col items-center">
                <div
                    class="flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 mb-6">
                    <flux:icon.sparkles class="w-8 h-8" />
                </div>
                <h3 class="text-xl font-bold text-zinc-900 dark:text-white mb-2">Beautiful Design</h3>
                <p class="text-zinc-600 dark:text-zinc-400">
                    Use icons, brief titles, and benefit-led text to explain why users should pick your app.
                </p>
            </div>

        </div>
    </div>
</section>

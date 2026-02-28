<section class="py-24 bg-zinc-50 dark:bg-zinc-900/50 {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                {{ $attributes['sectionTitle'] ?? 'Review Section' }}
            </h2>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                {{ $attributes['description'] ?? 'Let happy users convince the rest.' }}
            </p>
            <div class="mt-6 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Review 1 --}}
            <div
                class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300 italic mb-6">
                        "Testimonials with names, ratings, and short blurbs help build authenticity and trust. This app
                        completely changed my workflow."
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <img class="w-10 h-10 rounded-full" src="https://i.pravatar.cc/150?u=a042581f4e29026024d"
                        alt="User Avatar">
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">Alex Johnson</h4>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">United States</span>
                    </div>
                </div>
            </div>

            {{-- Review 2 --}}
            <div
                class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300 italic mb-6">
                        "Incredibly fast and beautifully designed. The development team really knew what they were doing
                        when they built this."
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <img class="w-10 h-10 rounded-full" src="https://i.pravatar.cc/150?u=a04258a2462d826712d"
                        alt="User Avatar">
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">Maria Garcia</h4>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Spain</span>
                    </div>
                </div>
            </div>

            {{-- Review 3 --}}
            <div
                class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star class="w-5 h-5" />
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300 italic mb-6">
                        "We integrated this into our daily operations and saw a 40% increase in productivity. Highly
                        recommended for remote teams."
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <img class="w-10 h-10 rounded-full" src="https://i.pravatar.cc/150?u=a04258114e29026702d"
                        alt="User Avatar">
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">David Chen</h4>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">Canada</span>
                    </div>
                </div>
            </div>

            {{-- Review 4 --}}
            <div
                class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                        <flux:icon.star variant="solid" class="w-5 h-5" />
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-300 italic mb-6">
                        "The support is fantastic and the features exactly match what we need. Cannot imagine going back
                        to our old system."
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <img class="w-10 h-10 rounded-full" src="https://i.pravatar.cc/150?u=a048581f4e29026701d"
                        alt="User Avatar">
                    <div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white">Sarah Williams</h4>
                        <span class="text-xs text-zinc-500 dark:text-zinc-400">UK</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

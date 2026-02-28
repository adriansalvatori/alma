<section class="py-24 {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-3 lg:gap-12">

            <div class="lg:col-span-1 mb-8 lg:mb-0 text-center lg:text-left">
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                    {{ $attributes['sectionTitle'] ?? 'FAQ Section' }}
                </h2>
                <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                    {{ $attributes['description'] ?? 'Reduce hesitation with smart answers. Use collapsible questions to address common concerns without overwhelming the layout.' }}
                </p>
                <div class="mt-8">
                    <flux:button variant="subtle">Contact Support</flux:button>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">

                {{-- Question 1 --}}
                <div x-data="{ open: false }"
                    class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                    <button @click="open = !open"
                        class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                        <span class="font-bold text-zinc-900 dark:text-white">Is this app free to use?</span>
                        <flux:icon.plus x-show="!open" class="w-5 h-5 text-zinc-400" />
                        <flux:icon.minus x-show="open" class="w-5 h-5 text-zinc-400" x-cloak />
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">
                            Yes, there is a comprehensive free tier available for all new users. Premium features can be
                            unlocked via the Pro subscription.
                        </div>
                    </div>
                </div>

                {{-- Question 2 --}}
                <div x-data="{ open: false }"
                    class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                    <button @click="open = !open"
                        class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                        <span class="font-bold text-zinc-900 dark:text-white">How does the 30-day money back guarantee
                            work?</span>
                        <flux:icon.plus x-show="!open" class="w-5 h-5 text-zinc-400" />
                        <flux:icon.minus x-show="open" class="w-5 h-5 text-zinc-400" x-cloak />
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">
                            If you're not completely satisfied with our service within the first 30 days, simply contact
                            our support team to receive a full refund, no questions asked.
                        </div>
                    </div>
                </div>

                {{-- Question 3 --}}
                <div x-data="{ open: false }"
                    class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                    <button @click="open = !open"
                        class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                        <span class="font-bold text-zinc-900 dark:text-white">What platforms do you support?</span>
                        <flux:icon.plus x-show="!open" class="w-5 h-5 text-zinc-400" />
                        <flux:icon.minus x-show="open" class="w-5 h-5 text-zinc-400" x-cloak />
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">
                            Our application is fully supported on iOS, Android, macOS, Windows, and modern web browsers.
                        </div>
                    </div>
                </div>

                {{-- Question 4 --}}
                <div x-data="{ open: false }"
                    class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                    <button @click="open = !open"
                        class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                        <span class="font-bold text-zinc-900 dark:text-white">Can I cancel my subscription
                            anytime?</span>
                        <flux:icon.plus x-show="!open" class="w-5 h-5 text-zinc-400" />
                        <flux:icon.minus x-show="open" class="w-5 h-5 text-zinc-400" x-cloak />
                    </button>
                    <div x-show="open" x-collapse x-cloak>
                        <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">
                            Absolutely. You can cancel or pause your membership directly from your account dashboard
                            with zero hidden fees.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

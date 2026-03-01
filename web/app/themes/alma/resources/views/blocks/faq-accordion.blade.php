<div class="{{ $block->classes }} py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-3 lg:gap-12">

            <div class="lg:col-span-1 mb-8 lg:mb-0 text-center lg:text-left">
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                    {{ $sectionTitle }}
                </h2>
                <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                    {{ $description }}
                </p>
                <div class="mt-8">
                    @isset($block)
                        {!! '<InnerBlocks />' !!}
                    @else
                        <flux:button variant="subtle">Contact Support</flux:button>
                    @endisset
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">

                @foreach ($faqs as $index => $faq)
                    <div x-data="{ open: false }"
                        class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
                        <button @click="open = !open"
                            class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                            <span class="font-bold text-zinc-900 dark:text-white">{!! $faq['question'] !!}</span>
                            <flux:icon.plus x-show="!open" class="w-5 h-5 text-zinc-400" />
                            <flux:icon.minus x-show="open" class="w-5 h-5 text-zinc-400" x-cloak />
                        </button>
                        <div x-show="open" x-collapse x-cloak>
                            <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">
                                {!! $faq['answer'] !!}
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>

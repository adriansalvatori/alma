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
                    @if (!empty(trim($content ?? '')))
                        {!! $content !!}
                    @else
                        <flux:button variant="subtle">Contact Support</flux:button>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">

                @php
                    $faqs = $attributes['faqs'] ?? [
                        [
                            'question' => 'Is this app free to use?',
                            'answer' =>
                                'Yes, there is a comprehensive free tier available for all new users. Premium features can be unlocked via the Pro subscription.',
                        ],
                        [
                            'question' => 'How does the 30-day money back guarantee work?',
                            'answer' =>
                                'If you\'re not completely satisfied with our service within the first 30 days, simply contact our support team to receive a full refund, no questions asked.',
                        ],
                        [
                            'question' => 'What platforms do you support?',
                            'answer' =>
                                'Our application is fully supported on iOS, Android, macOS, Windows, and modern web browsers.',
                        ],
                        [
                            'question' => 'Can I cancel my subscription anytime?',
                            'answer' =>
                                'Absolutely. You can cancel or pause your membership directly from your account dashboard with zero hidden fees.',
                        ],
                    ];
                @endphp

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
</section>

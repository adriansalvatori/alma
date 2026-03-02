{{--
{
    "name": "alma/faq-accordion",
    "title": "Faq Accordion",
    "description": "An accordion list of frequently asked questions.",
    "category": "alma",
    "icon": "editor-help",
    "supports": {
        "align": true,
        "multiple": true,
        "jsx": true,
        "color": {
            "background": true,
            "text": true,
            "gradient": true
        }
    },
    "attributes": {
        "sectionTitle": {
            "type": "string",
            "control": "TextControl",
            "label": "Section Title",
            "default": "FAQ Section"
        },
        "description": {
            "type": "string",
            "control": "TextareaControl",
            "label": "Description",
            "default": "Reduce hesitation with smart answers. Use collapsible questions to address common concerns without overwhelming the layout."
        },
        "q1": { "type": "string", "control": "TextControl", "label": "Question 1", "default": "Is this app free to use?" },
        "a1": { "type": "string", "control": "TextareaControl", "label": "Answer 1", "default": "Yes, there is a comprehensive free tier available for all new users. Premium features can be unlocked via the Pro subscription." },
        "q2": { "type": "string", "control": "TextControl", "label": "Question 2", "default": "How does the 30-day money back guarantee work?" },
        "a2": { "type": "string", "control": "TextareaControl", "label": "Answer 2", "default": "If you're not completely satisfied with our service within the first 30 days, simply contact our support team to receive a full refund, no questions asked." },
        "q3": { "type": "string", "control": "TextControl", "label": "Question 3", "default": "What platforms do you support?" },
        "a3": { "type": "string", "control": "TextareaControl", "label": "Answer 3", "default": "Our application is fully supported on iOS, Android, macOS, Windows, and modern web browsers." },
        "q4": { "type": "string", "control": "TextControl", "label": "Question 4", "default": "Can I cancel my subscription anytime?" },
        "a4": { "type": "string", "control": "TextareaControl", "label": "Answer 4", "default": "Absolutely. You can cancel or pause your membership directly from your account dashboard with zero hidden fees." },
        "q5": { "type": "string", "control": "TextControl", "label": "Question 5" },
        "a5": { "type": "string", "control": "TextareaControl", "label": "Answer 5" }
    }
}
--}}

@php
    $sectionTitle = $attributes['sectionTitle'] ?? 'FAQ Section';
    $description = $attributes['description'] ?? 'Reduce hesitation...';

    $faqs = [];
    for ($i = 1; $i <= 5; $i++) {
        $q = $attributes["q{$i}"] ?? '';
        $a = $attributes["a{$i}"] ?? '';
        if ($q && $a) {
            $faqs[] = ['question' => $q, 'answer' => $a];
        }
    }
@endphp

<div class="{{ $block->classes ?? 'wp-block-alma-faq-accordion' }} py-24">
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
                    <flux:button variant="subtle" href="#">Contact Support</flux:button>
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

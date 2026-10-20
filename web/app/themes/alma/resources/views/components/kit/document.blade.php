{{-- A page of text in numbered sections (a method, payments, terms): eyebrow, title and lead, then each section's title beside its paragraphs. sections: [['title' => …, 'body' => [html paragraph, …]], …]; paragraphs go through wp_kses_post. --}}
@props(['eyebrow' => '', 'title', 'lead' => '', 'sections' => [], 'updated' => null])

<section {{ $attributes->class(['pb-section pt-12 md:pt-20']) }}>
    <div class="container-page">
        @if ($eyebrow !== '')
            <p class="type-eyebrow mb-5 opacity-60">{{ $eyebrow }}</p>
        @endif
        <h1 class="type-display max-w-[14ch]">{{ $title }}</h1>
        @if ($lead !== '')
            <p class="type-lead mt-8 max-w-3xl opacity-70">{{ $lead }}</p>
        @endif

        <div class="mt-16 md:mt-24">
            @foreach ($sections as $section)
                <div class="grid gap-6 border-t border-current/15 py-10 md:grid-cols-12">
                    <p class="type-eyebrow opacity-50 md:col-span-2">{{ sprintf('(%02d)', $loop->iteration) }}</p>
                    <h2 class="type-title md:col-span-4">{{ $section['title'] }}</h2>
                    <div class="type-body space-y-4 opacity-80 md:col-span-6 [&_a]:underline [&_a]:underline-offset-4">
                        @foreach ($section['body'] as $paragraph)
                            <p>{!! wp_kses_post($paragraph) !!}</p>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @if ($updated)
            <p class="type-body border-t border-current/15 pt-8 opacity-50">{{ $updated }}</p>
        @endif
    </div>
</section>

{{-- FAQ: heading and intro beside an accordion of questions, with an optional contact link and FAQPage JSON-LD. --}}
<section {{ $attributes->class(['py-section']) }}>
  <div class="container-page lg:grid lg:grid-cols-3 lg:gap-12">
    <div class="mb-8 text-center lg:mb-0 lg:text-left">
      <h2 class="text-heading font-bold tracking-tight text-zinc-900 dark:text-white">{{ $heading }}</h2>
      @if ($intro)
        <p class="text-lead mt-4 text-zinc-600 dark:text-zinc-400">{{ $intro }}</p>
      @endif
      @if ($contact['text'])
        <a href="{{ $contact['url'] }}" class="mt-6 inline-flex items-center gap-2 font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
          {{ $contact['text'] }}
          <flux:icon.arrow-right class="h-4 w-4" />
        </a>
      @endif
    </div>

    <div class="space-y-4 lg:col-span-2">
      @foreach ($items as $item)
        <details @class(['group rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50']) @if ($loop->first) open @endif>
          <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 font-bold text-zinc-900 dark:text-white">
            {{ $item['question'] }}
            <flux:icon.plus class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-45" />
          </summary>
          <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400">{!! wpautop(esc_html($item['answer'])) !!}</div>
        </details>
      @endforeach
    </div>
  </div>

  @if ($schema)
    <script type="application/ld+json">{!! $schema !!}</script>
  @endif
</section>

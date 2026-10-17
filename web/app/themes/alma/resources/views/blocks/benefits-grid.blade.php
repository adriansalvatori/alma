{{-- Benefits: a row of up to eight icon + title + sentence items under a section heading. --}}
<section {{ $attributes->class(['py-section']) }}>
  <div class="container-page">
    @include('partials.section-heading', ['heading' => $heading, 'intro' => $intro])

    <div class="grid grid-cols-1 items-start gap-12 text-center sm:grid-cols-2 lg:grid-cols-4">
      @foreach ($items as $item)
        <div class="flex flex-col items-center gap-3">
          <span class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            <flux:icon :name="$item['icon'] ?: 'bolt'" class="h-8 w-8" />
          </span>
          <h3 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $item['title'] }}</h3>
          <p class="text-zinc-600 dark:text-zinc-400">{{ $item['text'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

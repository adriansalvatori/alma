{{-- Features grid: section heading over two or three columns of feature cards, optionally with the first card spanning the row. --}}
<section {{ $attributes->class(['bg-zinc-50 py-section dark:bg-zinc-900/50']) }}>
  <div class="container-page">
    @include('partials.section-heading', ['heading' => $heading, 'intro' => $intro])

    <div @class([
      'grid grid-cols-1 gap-6 md:grid-cols-2',
      'lg:grid-cols-3' => $columns === 3,
    ])>
      @foreach ($items as $item)
        @php($featured = $featureFirst && $loop->first)

        <article @class([
          'flex overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900',
          'flex-col md:col-span-full md:flex-row' => $featured,
          'flex-col' => ! $featured,
        ])>
          <div @class(['flex flex-1 flex-col gap-3 p-8', 'justify-center md:w-1/2' => $featured])>
            @if (! $item['image'])
              <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                <flux:icon :name="$item['icon'] ?: 'bolt'" class="h-6 w-6" />
              </span>
            @endif
            <h3 @class(['font-bold text-zinc-900 dark:text-white', 'text-title' => $featured, 'text-xl' => ! $featured])>{{ $item['title'] }}</h3>
            <p class="text-zinc-600 dark:text-zinc-400">{{ $item['text'] }}</p>
          </div>

          @if ($item['image'])
            <div @class(['bg-zinc-100 dark:bg-zinc-800', 'md:w-1/2' => $featured, 'aspect-video' => ! $featured])>
              {!! $item['image'] !!}
            </div>
          @endif
        </article>
      @endforeach
    </div>
  </div>
</section>

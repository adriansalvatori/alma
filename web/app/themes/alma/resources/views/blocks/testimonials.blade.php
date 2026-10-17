{{-- Testimonials: section heading over cards of customer quotes with name, role, optional star rating and photo or initials. --}}
<section {{ $attributes->class(['bg-zinc-50 py-section dark:bg-zinc-900/50']) }}>
  <div class="container-page">
    @include('partials.section-heading', ['heading' => $heading, 'intro' => $intro])

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
      @foreach ($items as $item)
        <figure class="flex flex-col justify-between gap-6 rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
          <div class="flex flex-col gap-4">
            @if ($showRatings && $item['rating'])
              <div class="flex gap-1 text-amber-400" role="img" aria-label="{{ sprintf(__('%d out of 5 stars', 'alma'), $item['rating']) }}">
                @for ($star = 1; $star <= 5; $star++)
                  <flux:icon.star variant="{{ $star <= $item['rating'] ? 'solid' : 'outline' }}" class="h-5 w-5" />
                @endfor
              </div>
            @endif
            <blockquote class="text-zinc-700 dark:text-zinc-300">“{{ $item['quote'] }}”</blockquote>
          </div>

          <figcaption class="flex items-center gap-4">
            @if ($item['photo'])
              {!! $item['photo'] !!}
            @else
              <span aria-hidden="true" class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700 dark:bg-brand-500/20 dark:text-brand-300">{{ $item['initials'] }}</span>
            @endif
            <span>
              <span class="block text-sm font-bold text-zinc-900 dark:text-white">{{ $item['name'] }}</span>
              @if ($item['role'])
                <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ $item['role'] }}</span>
              @endif
            </span>
          </figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>

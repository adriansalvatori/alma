{{-- Logo cloud: one line of social proof beside a row of client logos (wordmarks until logos are uploaded). --}}
<section {{ $attributes->class(['py-12']) }}>
  <div class="container-page flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
    @if ($text)
      <p class="text-sm font-semibold uppercase tracking-wide text-zinc-500 md:w-1/3">{{ $text }}</p>
    @endif

    <ul @class([
      'grid grid-cols-2 items-center gap-8 md:flex-1 md:grid-cols-4',
      'opacity-60 grayscale transition duration-300 hover:opacity-100 hover:grayscale-0' => $grayscale,
    ])>
      @foreach ($logos as $logo)
        <li class="flex justify-center md:justify-start">
          @if ($logo['url'])<a href="{{ $logo['url'] }}" class="inline-flex" rel="noopener">@endif
            @if ($logo['image'])
              {!! $logo['image'] !!}
            @else
              <span class="text-xl font-bold tracking-tight text-zinc-700 dark:text-zinc-300">{{ $logo['name'] }}</span>
            @endif
          @if ($logo['url'])</a>@endif
        </li>
      @endforeach
    </ul>
  </div>
</section>

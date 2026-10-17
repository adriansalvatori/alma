{{-- Hero: page opener with headline, supporting line, up to two buttons and an optional image, in four layouts. --}}
@php
  $buttons = array_filter([
    $primaryCta['text'] ? ['variant' => 'primary', ...$primaryCta] : null,
    $secondaryCta['text'] ? ['variant' => 'outline', ...$secondaryCta] : null,
  ]);
@endphp

<section {{ $attributes->class([
  'relative w-full overflow-hidden',
  'min-h-dvh flex flex-col justify-center' => $fullHeight,
]) }}>
  @if ($layout === 'cinematic')
    <div class="absolute inset-0 -z-10 bg-zinc-100 dark:bg-zinc-900">
      @if ($image)
        <div class="h-full w-full opacity-30">{!! $image !!}</div>
      @endif
      <div class="absolute inset-0 bg-gradient-to-t from-white via-white/80 to-transparent dark:from-zinc-950 dark:via-zinc-950/60"></div>
    </div>
  @elseif ($layout === 'glow')
    <div aria-hidden="true" class="pointer-events-none absolute left-1/2 top-0 -z-10 h-[400px] w-[800px] -translate-x-1/2 -translate-y-12 rounded-full bg-gradient-to-r from-brand-500 via-purple-500 to-pink-500 opacity-25 blur-3xl"></div>
  @endif

  <div @class([
    'container-page py-section',
    'grid items-center gap-12 lg:grid-cols-2' => $layout === 'split',
    'text-center' => in_array($layout, ['centered', 'glow'], true),
  ])>
    <div @class([
      'flex flex-col gap-6',
      'items-center mx-auto max-w-3xl' => in_array($layout, ['centered', 'glow'], true),
      'max-w-3xl' => $layout === 'cinematic',
      'items-start' => $layout !== 'centered' && $layout !== 'glow',
    ])>
      @if ($eyebrow)
        <p class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white/60 px-3 py-1 text-sm font-medium text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900/60 dark:text-zinc-200">
          <span class="h-2 w-2 rounded-full bg-brand-500"></span>
          {{ $eyebrow }}
        </p>
      @endif

      <h1 class="text-display font-bold tracking-tight text-zinc-900 dark:text-white">{{ $headline }}</h1>

      @if ($subheadline)
        <p class="text-lead max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $subheadline }}</p>
      @endif

      @if ($buttons)
        <div class="flex flex-wrap items-center gap-4 pt-2">
          @foreach ($buttons as $button)
            <flux:button :href="$button['url']" :variant="$button['variant']">{{ $button['text'] }}</flux:button>
          @endforeach
        </div>
      @endif

      @if ($proofText)
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $proofText }}</p>
      @endif
    </div>

    @if ($layout === 'split')
      <div class="relative aspect-[4/5] w-full overflow-hidden rounded-3xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900">
        @if ($image)
          {!! $image !!}
        @else
          <div class="flex h-full items-center justify-center text-zinc-400 dark:text-zinc-600">
            <flux:icon.photo class="h-16 w-16" />
          </div>
        @endif
      </div>
    @elseif ($image && in_array($layout, ['centered', 'glow'], true))
      <div class="mx-auto mt-16 max-w-5xl overflow-hidden rounded-2xl shadow-2xl ring-1 ring-zinc-900/10 dark:ring-white/10">
        {!! $image !!}
      </div>
    @endif
  </div>
</section>

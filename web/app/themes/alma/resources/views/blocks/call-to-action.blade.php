{{-- Call to action: closing panel (neutral or brand colour) with heading, sentence, up to two buttons and an optional image. --}}
@php($brand = $style === 'brand')

<section {{ $attributes->class(['py-section']) }}>
  <div class="container-page">
    <div @class([
      'grid overflow-hidden rounded-3xl',
      'lg:grid-cols-2' => $image,
      'border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900' => ! $brand,
      'bg-brand-600 text-white' => $brand,
    ])>
      <div @class(['flex flex-col justify-center gap-6 p-10 sm:p-16', 'items-center text-center' => ! $image])>
        <h2 @class(['text-heading font-bold tracking-tight', 'text-zinc-900 dark:text-white' => ! $brand])>{{ $heading }}</h2>

        @if ($text)
          <p @class(['text-lead max-w-xl', 'text-zinc-600 dark:text-zinc-400' => ! $brand, 'text-white/85' => $brand])>{{ $text }}</p>
        @endif

        <div class="flex flex-wrap items-center gap-4">
          @if ($primaryCta['text'])
            <flux:button :href="$primaryCta['url']" :variant="$brand ? 'filled' : 'primary'">{{ $primaryCta['text'] }}</flux:button>
          @endif
          @if ($secondaryCta['text'])
            <flux:button :href="$secondaryCta['url']" variant="ghost" :class="$brand ? 'text-white!' : ''">{{ $secondaryCta['text'] }}</flux:button>
          @endif
        </div>
      </div>

      @if ($image)
        <div class="hidden min-h-80 lg:block">{!! $image !!}</div>
      @endif
    </div>
  </div>
</section>

{{-- Marquee: words in display type sliding sideways forever; reverse runs the other way. Still for visitors who ask for reduced motion. Decorative, so hidden from assistive tech. --}}
@props(['items' => [], 'reverse' => false, 'separator' => '✦'])

<div {{ $attributes->class(['overflow-hidden']) }} aria-hidden="true">
    <div @class(['kit-marquee-track flex w-max', 'is-reverse' => $reverse])>
        @foreach ([1, 2] as $copy)
            <p class="type-hero flex shrink-0 items-center gap-[0.35em] whitespace-nowrap pr-[0.35em]">
                @foreach ($items as $item)
                    <span>{{ $item }}</span><span class="text-brand-500">{{ $separator }}</span>
                @endforeach
            </p>
        @endforeach
    </div>
</div>

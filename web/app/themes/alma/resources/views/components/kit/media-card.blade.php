{{-- Media card: a 4:3 panel (an image, or a short video that plays only while hovered or focused, js/kit/hover-video.js), then a title, a meta line and an arrow. With page transitions on, following it grows the panel into the next page (js/kit/transitions.js). image: an attachment ID or a URL. --}}
@props(['href', 'title', 'meta' => '', 'image' => null, 'video' => null, 'alt' => ''])

@php
    $media = 'absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.03]';
    $poster = is_numeric($image) ? wp_get_attachment_image_url((int) $image, 'large') : $image;
@endphp

<a href="{{ $href }}" @navigate data-morph @if ($video) data-hover-video @endif {{ $attributes->class(['group block']) }}>
    <div class="relative aspect-[4/3] overflow-hidden rounded-3xl bg-zinc-100 dark:bg-white/5" data-morph-media>
        @if ($video)
            <video class="{{ $media }}" muted loop playsinline preload="none" aria-hidden="true" poster="{{ $poster }}" src="{{ $video }}"></video>
        @elseif (is_numeric($image))
            {!! \App\Support\ResponsiveImage::html((int) $image, 'large', ['class' => $media, 'alt' => $alt, 'sizes' => '(max-width: 768px) 85vw, 40vw']) !!}
        @elseif ($image)
            <img src="{{ $image }}" alt="{{ $alt }}" width="1200" height="900" class="{{ $media }}" loading="lazy">
        @endif
    </div>

    <div class="mt-5 flex items-start justify-between gap-6">
        <div>
            <h3 class="type-title">{{ $title }}</h3>
            @if ($meta !== '')
                <p class="type-body mt-2 opacity-60">{{ $meta }}</p>
            @endif
        </div>
        <span aria-hidden="true" class="mt-1 inline-flex size-11 shrink-0 items-center justify-center rounded-full border border-current/25 transition-colors group-hover:border-brand-500 group-hover:bg-brand-500 group-hover:text-white">→</span>
    </div>
</a>

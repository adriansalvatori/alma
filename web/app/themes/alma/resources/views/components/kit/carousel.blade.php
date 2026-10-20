{{-- Carousel: one row of fixed-width cards you drag (js/kit/carousel.js); they ease and squash with speed. inset lines the first card up with the page container while dragged cards still run to the screen edge. Put <x-kit.carousel-item> inside. --}}
@props(['inset' => false, 'width' => '38vw', 'widthMobile' => '85vw'])

<div {{ $attributes->class(['kit-carousel', 'is-inset' => $inset]) }} style="--kit-carousel-width: {{ $width }}; --kit-carousel-width-mobile: {{ $widthMobile }}">
    <div class="kit-carousel-track">
        {{ $slot }}
    </div>
</div>

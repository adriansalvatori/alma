<div class="container mt-6 mb-6">
    <h2 class="title is-5">{!! $title !!}</h2>
</div>
<div class="carousel"
    style="--carousel-item-width: {!! $itemWidth !!}; --carousel-item-mobile-width: {!! $itemWidthMobile !!}">
    <div class="carousel-wrapper columns is-mobile">
        {{ $slot }}
    </div>
</div>

@pushOnce('css')
    <style>
        .carousel {
            width: 100vw;
            cursor: grab;

            &.dragging {
                cursor: grabbing;
            }

            .carousel-item {
                max-width: var(--carousel-item-width);
                user-select: none;

                @media only screen and (max-width:var(--tablet)) {
                    max-width: var(--carousel-item-mobile-width)
                }
            }
        }
    </style>
@endpushOnce

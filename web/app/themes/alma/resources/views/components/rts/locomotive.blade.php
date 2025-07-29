<script src="https://cdn.jsdelivr.net/npm/locomotive-scroll@beta/bundled/locomotive-scroll.min.js"></script>
<script>
    (function() {
        const locomotiveScroll = new LocomotiveScroll({
            lenisOptions: {
                lerp: 0.1,
                duration: 0.6,
                orientation: 'vertical',
                gestureOrientation: 'vertical',
                smoothWheel: true,
                smoothTouch: false,
                wheelMultiplier: 1,
                touchMultiplier: 2,
                normalizeWheel: true,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 *
                t)), // https://www.desmos.com/calculator/brs54l4xou
            },
        });
    })();
</script>

<style>
    html.lenis,
    html.lenis body {
        height: auto
    }

    .lenis.lenis-smooth {
        scroll-behavior: auto !important
    }

    .lenis.lenis-smooth [data-lenis-prevent] {
        overscroll-behavior: contain
    }

    .lenis.lenis-stopped {
        overflow: hidden
    }

    .lenis.lenis-smooth iframe {
        pointer-events: none
    }
</style>

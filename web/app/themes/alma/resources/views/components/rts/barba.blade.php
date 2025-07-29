<!-- unpkg -->
<script src="https://unpkg.com/@barba/core"></script>

<!-- jsdelivr -->
<script src="https://cdn.jsdelivr.net/npm/@barba/core"></script>

<script>
    barba.init({
        cacheFirstPage: false,
        ccacheIgnore: ['/carrito/', '/cart', '/checkout/'],
        debug: {{ app()->environment('development') ? 'true' : 'false' }},
        logLevel: 'off',
        prefetchIgnore: false,
        prevent: ({
            el
        }) => el.classList && (el.classList.contains('ab-item') || el.hasAttribute('href') && el
            .getAttribute('href').includes('wp-admin')),
        preventRunning: false,
        timeout: 2e3,
        transitions: [{
            enter() {},
            beforeEnter: ({ next}) => {
                const matches = next.html.match(/<body.+?class="([^""]*)"/i);
                document.body.setAttribute('class', (matches && matches.at(1)) ?? '');
            },
            afterEnter: () => {
                window.scrollTo({
                    top: 0
                });
                document.dispatchEvent(new Event('DOMContentLoaded'));
                window.dispatchEvent(new Event('load'));
                if (typeof jQuery !== 'undefined') {
                    jQuery(document).trigger('ready');
                }
            }
        }]
        views: [],
    })
</script>

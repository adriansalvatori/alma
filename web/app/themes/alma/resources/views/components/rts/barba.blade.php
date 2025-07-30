<!-- unpkg -->
<script src="https://unpkg.com/@barba/core"></script>

<script>
    barba.hooks.after(() => { // This is supposed to fire events
        console.log('Firing events')
        document.dispatchEvent(new Event('DOMContentLoaded'));
        window.dispatchEvent(new Event('load'));
        if (typeof jQuery !== 'undefined') {
            jQuery(document).trigger('ready');
        }
    })

    const loaderElements = [
        document.querySelector('.preloader'),
        document.querySelector('.layout')
    ]

    loaderElements.forEach(el => el.classList.remove('is-loading'));

    const Transitions = [{
        beforeEnter: ({
            next
        }) => {
            const matches = next.html.match(/<body.+?class="([^""]*)"/i);
            document.body.setAttribute('class', (matches && matches.at(1)) ?? '');
        },
        enter() {
            // animate loading screen away
            loaderElements.forEach(el => el.classList.remove('is-loading'));
        },
        leave() {
            return new Promise((done) => {
                loaderElements.forEach(el => el.classList.add('is-loading'));
                setTimeout(() => {
                    done();
                }, 600);
            });
        },
    }]

    barba.init({
        cacheFirstPage: false,
        cacheIgnore: ['/carrito/', '/cart', '/checkout/'],
        debug: {{ app()->environment('development') ? 'true' : 'false' }},
        logLevel: 'off',
        prefetchIgnore: false,
        prevent: ({
            el
        }) => el.classList && (el.classList.contains('ab-item') || el.hasAttribute('href') && el
            .getAttribute('href').includes('wp-admin')),
        preventRunning: false,
        timeout: 2e3,
        transitions: Transitions,
        views: [],
    })
</script>

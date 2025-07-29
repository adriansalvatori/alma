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
        prevent: ({ el }) => el.classList && (el.classList.contains('ab-item') || el.hasAttribute('href') && el.getAttribute('href').includes('wp-admin')),
        preventRunning: false,
        timeout: 2e3,
        transitions: [],
        views: [],
    })
</script>

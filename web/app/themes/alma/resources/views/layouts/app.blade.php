<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @php(do_action('get_header'))
    @php(wp_head())
</head>

<body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
        <a class="sr-only focus:not-sr-only" href="#main">
            {{ __('Skip to content', 'sage') }}
        </a>

        @include('sections.header')

        <main id="main"
            class="main wp-block-group alignfull min-h-screen @if (!is_front_page()) px-4 sm:px-6 lg:px-8 py-12 @endif">
            @yield('content')
        </main>

        @hasSection('sidebar')
            <aside class="sidebar">
                @yield('sidebar')
            </aside>
        @endif

        @include('sections.footer')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
</body>

</html>

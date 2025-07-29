<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-rts.all />
</head>

<body @php(body_class())>
    @php(wp_body_open())

    <div data-scroll id="app">
        <a class="sr-only focus:not-sr-only" href="#main">
            {{ __('Skip to content', 'sage') }}
        </a>

        @include('sections.header')

        <main style="height: 100vh;">
            <div>
                <h1>Hello 👋</h1>
            </div>
            <div>
                <h2 data-scroll data-scroll-speed="0.5">What's up?</h2>
                <p data-scroll data-scroll-speed="0.8">😬</p>
            </div>
        </main>

        <main id="main" class="main">
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
    @livewireScripts
</body>

</html>

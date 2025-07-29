<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body @php(body_class()) data-barba="wrapper">
    @php(wp_body_open())

    <div id="app">
        <a class="sr-only focus:not-sr-only" href="#main">
            {{ __('Skip to content', 'sage') }}
        </a>

        @include('sections.header')

        <main id="main" class="main" data-barba="container" data-barba-namespace="home">
            @yield('content')
        </main>

        @hasSection('sidebar')
            <aside class="sidebar">
                @yield('sidebar')
            </aside>
        @endif

        @include('sections.footer')
    </div>
    <x-rts.all />
    @php(do_action('get_footer'))
    @php(wp_footer())
    @livewireScripts

</body>

</html>

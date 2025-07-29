<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body @php(body_class()) data-barba="wrapper">
    @php(wp_body_open())

    <div id="app">
        <main id="main" class="main" data-barba="container" data-barba-namespace="home">
            @include('sections.header')
            @yield('content')

            @hasSection('sidebar')
                <aside class="sidebar">
                    @yield('sidebar')
                </aside>
            @endif
            @include('sections.footer')
        </main>
    </div>
    @livewireScripts
    <x-rts.all />
    @php(do_action('get_footer'))
    @php(wp_footer())
</body>
</html>

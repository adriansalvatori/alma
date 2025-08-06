<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php(do_action('get_header'))
    @php(wp_head())
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @livewireStyles
    @stack('meta')
    @stack('css')
</head>

<body @php(body_class()) data-barba="wrapper">
    @php(wp_body_open())
    <div id="app">
        <x-loader />
        @include('sections.navigation')
        <main id="main" class="main layout" data-barba="container" data-barba-namespace="home">
            @yield('content')
        </main>
        @include('sections.footer')
    </div>
    @livewireScripts
    <x-rts.all />
    @stack('js')
    @php(do_action('get_footer'))
    @php(wp_footer())
</body>

</html>

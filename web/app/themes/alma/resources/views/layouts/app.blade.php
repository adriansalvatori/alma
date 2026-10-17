{{-- Base layout: every front-end page. The <head> order is deliberate (docs/design-system.md, "Performance"). --}}
<!doctype html>
<html @php(language_attributes())>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Sets the dark class before first paint, so a dark-mode visitor never sees a white flash. --}}
  @fluxAppearance

  {{-- 1. The render-blocking stylesheet, as early as possible. --}}
  @vite(['resources/css/app.css'])

  {{-- 2. The LCP image slot. A view that knows its largest image pushes one preload here:
       @push('preload') <link rel="preload" as="image" href="..." fetchpriority="high"> @endpush
       One URL, no media-query pairs: the preload scanner evaluates media against its default viewport. --}}
  @stack('preload')

  {{-- 3. Self-hosted fonts used above the fold on every page (config/content.php). --}}
  @include('partials.font-preloads')

  @livewireStyles

  @php(do_action('get_header'))
  @php(wp_head())
</head>

<body @php(body_class())>
  @php(wp_body_open())

  <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-60 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:text-zinc-900">
    {{ __('Skip to content', 'alma') }}
  </a>

  <div id="app">
    @include('sections.header')

    <main id="main" class="main min-h-[60vh]">
      @yield('content')
    </main>

    @hasSection('sidebar')
      <aside class="sidebar container-page">
        @yield('sidebar')
      </aside>
    @endif

    @include('sections.footer')
  </div>

  @php(do_action('get_footer'))

  @livewireScripts
  @fluxScripts
  @vite(['resources/js/app.js'])

  @php(wp_footer())
</body>
</html>

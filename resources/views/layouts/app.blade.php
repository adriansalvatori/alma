@include('partials.header')

<div data-inertia-container>
  <main data-inertia-section class="main">
    @yield('content')
  </main>

  @hasSection('sidebar')
  <aside class="sidebar">
    @yield('sidebar')
  </aside>
  @endif

  @include('partials.footer')
</div>

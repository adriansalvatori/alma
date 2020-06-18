@include('partials.header')

<main class="main">
  @yield('content')
</main>

@hasSection('sidebar')
<aside class="sidebar">
  @yield('sidebar')
</aside>
@endif

@include('partials.footer')

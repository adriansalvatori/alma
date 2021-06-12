@include('partials.header')

<div class="scrollContainer">
  <main class="main">
    @yield('content')
  </main>
  
  @hasSection('sidebar')
  <aside class="sidebar">
    @yield('sidebar')
  </aside>
  @endif
  
  @include('partials.footer')  
</div>
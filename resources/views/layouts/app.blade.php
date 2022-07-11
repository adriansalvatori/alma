@include('partials.header')
<div data-inertia-container>
  <main data-inertia-section class="main">
    <div data-solar="container">
      @yield('content')
    </div>
  </main>
  @include('partials.footer')
</div>

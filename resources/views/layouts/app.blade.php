@include('partials.loader')
@include('partials.header')
@include('partials.menu')
<div data-inertia-container>
  <main data-inertia-section class="main has-padding-left-40">
    <div data-solar="container">
      @yield('content')
    </div>
  </main>
</div>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <?php do_action('get_header'); ?>

  <div id="app" data-solar="wrapper">
    <x-loader/>
    <div data-inertia-container class="layout">
        <main data-inertia-section class="main is-clipped" id="main">
          {{-- <x-navigation/> --}}
          <div data-solar="container" data-solar-namespace="home">
            @if(App\show_maintainance_mode())
              <x-maintainance-mode/>
            @else
              @yield('content')
              @include('partials.footer')
            @endif
          </div>
        </main>
    </div>
  </div>
  
  <?php do_action('get_footer'); ?>
  <?php wp_footer(); ?>
</body>

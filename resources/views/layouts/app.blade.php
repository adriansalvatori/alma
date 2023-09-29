<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <?php do_action('get_header'); ?>

  <div id="app" data-solar="wrapper">
    <x-loader/>
    <div data-inertia-container class="layout">
        <main data-inertia-section class="main" id="main">
          {{-- <x-navigation/> --}}
          <div data-solar="container" data-solar-namespace="home">
            @yield('content')
            @include('partials.footer')
          </div>
        </main>
    </div>
  </div>
  
  <?php do_action('get_footer'); ?>
  <?php wp_footer(); ?>
</body>

<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="container">
    <div class="navbar-brand">
      <a class="navbar-item title is-3 has-text-weight-bold" href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>
      <button class="button navbar-burger" data-target="navbarMenu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
    <div class="navbar-menu" id="navbarMenu">
      <div class="navbar-end">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'navbar-item', 'echo' => false]) !!}
        @endif
      </div>
    </div>
  </div>
</nav>


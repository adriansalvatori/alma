<nav class="navbar is-transparent" role="navigation" aria-label="main navigation">
  <div class="container" style="gap: 20px">
    <div class="navbar-brand">
      <a class="title mt-5 is-5 has-text-weight-bold" href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>
      <button class="button navbar-burger" data-target="navbarMenu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
    <div class="navbar-menu" id="navbarMenu">
      <div class="navbar-start">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'navbar-item', 'echo' => false]) !!}
        @endif
        <div class="navbar-item"><a href="{{ home_url('/welcome') }}">Explore</a></div>
        <div class="navbar-item"><a href="{{ home_url('/') }}">About</a></div>
      </div>
      <div class="navbar-end">
        <div class="navbar-item">
          <form role="search" method="get" class="search-form field is-grouped" action="{{ home_url('/') }}">
            <div class="control">
              <input
                type="search"
                placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'alma') !!}"
                value="{{ get_search_query() }}"
                name="s"
                class="input"
              >
            </div>
            <div class="control">
              <button type="submit" class="button is-primary">
                {{ _x('Search', 'submit button', 'alma') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>


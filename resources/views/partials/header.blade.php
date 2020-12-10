<div class="preheader has-text-right has-background-primary has-text-white has-padding-10">
  <div class="container">
    <div class="level-right">
      <span class="icon is-medium"><i data-feather="phone"></i></span><span>Llámanos: 000 000 000 | 000 000 000</span>
    </div>
  </div>
</div>

<nav class="navbar is-spaced is-dark" role="navigation" aria-label="main navigation" data-sticky data-sticky-offset="52">
  <div class="container">
    <div class="navbar-brand">
      <a class="level-item has-margin-right-50 has-padding-5" href="{{home_url()}}">
        <img src="@asset('images/logo.png')" width="150">
      </a>

      <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false"
        data-target="navbar-menu">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>

    <div id="navbar-menu" class="navbar-menu has-background-dark">
      <div class="navbar-start">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'navbar-item is-flex-desktop', 'echo' => false]) !!}
        @endif
      </div>
      <div class="navbar-end">
        <div class="navbar-item">
          @include('forms.search')
        </div>
        <div class="navbar-item">
          <div class="buttons">
            <a class="button is-light">
              Iniciar Sesión
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>
@if(!is_front_page())
  @include('partials.featured')
@endif
<footer class="hero is-dark is-medium">
  <div class="hero-body">
    <div class="container">
      <div class="column is-4 is-paddingless">
        <img src="{{get_option('alma_logo')}}" alt=""><br>
      </div>
      <div class="columns">
        <div class="column is-4 content is-small" data-aos="fade-up">
          <p class="has-margin-top-20">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Exercitationem sed similique eos repudiandae placeat voluptatibus, ex minima enim sit ad cum porro. Voluptatum reprehenderit voluptatibus doloribus exercitationem non facere dolore.</p>
        </div>

        <div class="column is-4" data-aos="fade-up">
          @include('components.contact')
        </div>

        <div class="column is-4" data-aos="fade-up">
          @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => '', 'echo' => false]) !!}
          @endif
        </div>
      </div>

      <hr class="has-margin-top-80 has-margin-bottom-90 is-opaque is-15">
      <div class="level is-paddingless">
        <div class="level-left" data-aos="fade-up">
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="facebook"></i></span></a>
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="twitter"></i></span></a>
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="instagram"></i></span></a>
        </div>
        <div class="level-right" data-aos="fade-up">
          <div class="column">
            Copyright ® Alma {{date(y)}}.
            Diseñado, Desarrolladoy y Mantenido por <a href="https://radikal.agency" class="link is-dark">Radikal
              Agency</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

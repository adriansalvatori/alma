@if (!is_home())
    
@include('partials.featured')

@endif
<footer class="hero is-dark is-medium">
  <div class="hero-body">
    <div class="container">
      <div class="column is-4 is-paddingless">
        <img src="@asset('images/logowhite.png')" alt=""><br>
      </div>
      <div class="columns">
        <div class="column is-4 content is-small">
          <p class="has-margin-top-20">Somos una compañía dedicada a la comercialización de los mejores inmuebles de las principales ciudades en
            Colombia y Estados Unidos. Estamos comprometidos en brindarles el mejor servicio a nuestros clientes y
            satisfacer sus necesidades.</p>
        </div>

        <div class="column is-4">

          <a href="tel:316 578 9930" class="button is-dark"><span class="icon"><i data-feather="smartphone"></i></span><span>316 578
              9930</span></a>
          <a href="tel:031 457 0940" class="button is-dark"><span class="icon"><i data-feather="phone"></i></span><span
              class="telefono">031 457 0940</span></a>
          <a href="mailto:info@elitebrokers.com.co" class="button is-dark"><span class="icon"><i
                data-feather="mail"></i></span><span>info@elitebrokers.com.co</span> </a>
          <a target="_blank" href="https://maps.google.com/?q=Carrera4Nro69a-09Bogotá,Colombia" class="button is-dark"><span class="icon"><i data-feather="map-pin"></i></span>
            <span>Carrera 4 # 69a - 09
              Bogotá, Colombia</span> </a>

        </div>

        <div class="column is-4">
          @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => '', 'echo' => false]) !!}
          @endif
        </div>
      </div>

      <hr class="has-margin-top-80 has-margin-bottom-90 is-opaque is-15">
      <div class="level is-paddingless">
        <div class="level-left">
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="facebook"></i></span></a>
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="twitter"></i></span></a>
          <a href="#" class="button is-dark"><span class="icon is-large"><i data-feather="instagram"></i></span></a>
        </div>
        <div class="level-right">
          <div class="column">
            Copyright ® Elite Brokers {{date(y)}}.
            Diseñado, Desarrolladoy y Mantenido por <a href="https://radikal.agency" class="link is-dark">Radikal
              Agency</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

<section class="hero is-fullheight has-overflow-x-hidden has-background-light">
  <div class="hero-body">
    <div class="container has-text-centered">
      <div class="card-content has-margin-top-100 has-margin-bottom-100">
        <h2 class="title is-2 has-text-uppercase">Explora nuestras propiedades</h2>
        <p class="has-margin-top-30">Contamos con un amplio portafolio de inmuebles para arriendo o venta como: <br>
          oficinas, bodegas, locales comerciales y vivienda.</p>
      </div>
      <div class="carousel columns featured-slider">
        @set($dc, 0)
        @set($index, 0)
        @set($deals, get_field('deals_destacados', 'options'))

        @query([
        'post_type'=> ['inversion-rentando', 'propiedades'],
        'post__in' => $deals,
        ])

        @posts()
        @php($index++)
        @if ($index == 1)
        <div class="column is-first">
          @include('components.card')
        </div>
        @else
        @php($dc++)
        @if ($dc === 1)
        <div class="column">
          @include('components.card')
          @endif
          @if ($dc === 2)
          @include('components.card')
        </div>
        @php($dc = 0 )
        @endif
        @endif
        @endposts
      </div>
    </div>
  </div>
</section>

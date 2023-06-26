<div data-cursor="-inverse" class="hero is-parallax-video is-fullheight is-primary" data-url="@asset('images/3263666610.mp4')">
    <div class="hero-body">
        <div class="container">
          <div class="columns">
            <div class="column is-4">
              <h1 class="title is-1">
                <div data-inertia data-inertia-reveal data-inertia-delay="600">Bienvenido a</div>
                <div data-inertia data-inertia-reveal data-inertia-delay="900">Movidagrafica</div></span>
              </h1>
              <p data-inertia data-inertia-reveal data-inertia-delay="1200" class="subtitle has-margin-top-20 has-margin-bottom-20">
                Aquí es donde las cosas magnificas empiezan a pasar. <b>¡Acompañanos en el viaje!</b>
              </p>
              <div class="buttons">
                <a href="" data-gravity data-cursor-stick data-cursor="-menu" class="button is-primary is-large has-padding-20"><span class="icon is-large"><i data-feather="user"></i></span></a>
                <a href="" data-gravity data-cursor-text="😈" class="button is-primary is-large has-padding-20">Inicia ahora</a>
                <a href="" data-gravity data-cursor-video="@asset('images/3263666610.mp4')" data-cursor-text="Esto es un Video" class="button is-large has-padding-20 is-white is-outlined">Quiero ver más</a>
                <a href="" data-gravity data-cursor-img="@asset('images/2471234.jpg')" data-cursor-text="Esto es una Imágen" class="button is-large has-padding-20 is-primary is-inverted">¿Quienes son ustedes?</a>
              </div>
            </div>
          </div>
          {{-- Estructura principal de el Boton estrella --}}
          <div>
            <a href="{{home_url('')}}" class="button is-inverse hover-estrella">
                @svg('orvita.svg');
                @svg('star.svg');
                <span class="has-margin-left-15">Empecemos</span>
            </a>  
          </div>
        </div>
    </div>
</div>

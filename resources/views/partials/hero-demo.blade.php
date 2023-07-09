<div class="hero is-light is-fullheight is-relative">
  <div class="is-overlay columns is-mobile">
    <div class="column is-3 is-offset-9 has-background-white" style="height: 100%"></div>
  </div>
  <div class="hero-header">
    <div class="container is-fluid">
      <div class="navbar" data-inertia data-inertia-reveal>
        <a href="{{ home_url() }}" data-gravity class="navbar-brand">
          <img class="iso has-margin-left-60" src="@asset('images/logo-primary.svg')" width=70>
          <img src="@asset('images/logo-primary.png')" width="250" class="has-margin-left-40 logo" alt="Logo Amerinode">
        </a>
        <div class="navbar-end">
          <div class="navbar-item">
            <a data-gravity href="https://telefonica.amerinode.net/Account/Login.aspx"
              class="button is-dark is-inverted">
              <span class="icon"><i data-feather="user"></i></span>
              <span>Area de Usuarios</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-body">
    <div class="container is-fluid">
      <div class="columns level">
        <div class="column is-6">
          <p data-inertia data-inertia-reveal data-inertia-delay="600"
            class="has-text-weight-normal subtitle has-text-dark is-italic has-margin-bottom-40">Opening your mind to
            the
            Better</p>
          <h1 class="title is-1 has-text-weight-normal has-text-dark">
            <div data-inertia data-inertia-reveal data-inertia-delay="700"><b>Soluciones E2E</b> diseñadas</div>
            <div data-inertia data-inertia-reveal data-inertia-delay="800">para la reducción significativa</div>
            <div data-inertia data-inertia-reveal data-inertia-delay="900">de <b>Opex y Capex</b>.</div></span>
          </h1>
        </div>
        <div class="column is-offset-1 is-6" data-cursor="-inverse">
          <div class="columns intro-card-container">
            <div data-inertia data-inertia-reveal class="column is-half">
              <a href="/portafolio-de-servicios/remanufactura" data-gravity data-cursor-text="Ver más"
                class="image is-4by3 has-background-danger"
                style="background-image: url('@asset('images/testing.jpg')') ; background-size: cover;">
                <span class="card-name has-margin-left-30 has-margin-bottom-30 has-text-white">Remanufactura</span>
              </a>
              <a href="/portafolio-de-servicios/gestion-de-repuestos" data-gravity data-cursor-text="Ver más"
                class="image has-margin-top-30 is-4by3 has-background-danger"
                style="background-image: url('@asset('images/reciclaje.jpg')') ; background-size: cover;">
                <span class="card-name has-margin-left-30 has-margin-bottom-30 has-text-white">Gestión de
                  Repuestos</span>
              </a>
            </div>
            <div data-inertia data-inertia-reveal class="column is-half">
              <a href="/portafolio-de-servicios/gestion-de-servicios" data-gravity data-cursor-text="Ver más"
                class="image is-3by5 has-background-danger"
                style="background-image: url('@asset('images/gestion-de-servicio.jpg')') ; background-size: cover; background-position:center;">
                <span class="card-name has-margin-left-30 has-margin-bottom-30 has-text-white">Gestión de
                  Servicios</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-foot">
    <div class="container is-fluid has-padding-left-80 has-padding-bottom-15">
      <div class="buttons">
        <a href="/portafolio-de-servicios" data-gravity data-inertia data-inertia-reveal data-inertia-delay="100"
          data-inertia-id="continue" data-direction="right" class="button is-dark is-inverted has-padding-20">
          <span class="icon"><i data-feather="arrow-right"></i></span>
          <span>Portafolio de Servicios</span>
        </a>
        <a href="/contactenos" data-gravity data-inertia data-inertia-reveal data-inertia-delay="200"
          class="button is-transparent is-light has-padding-20">
          <span class="icon"><i data-feather="disc"></i></span>
          <span>Contacto</span>
        </a>
      </div>
    </div>
  </div>
</div>

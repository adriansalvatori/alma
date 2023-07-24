@extends('layouts.app')

@section('content')

<div class="hero is-white is-fullheight">
  <div class="is-overlay columns is-gapless is-mobile">
    <div data-inertia data-inertia-reveal class="column is-2 is-offset-10 has-background-dark" style="height: 100vh">
    </div>
  </div>
  <div class="hero-header">
    <div class="container is-fluid">
      <div class="navbar" data-inertia data-inertia-reveal>
        <a href="{{home_url()}}" data-gravity class="navbar-brand">
          <img class="iso has-margin-left-60" src="@asset('images/iso.png')" width=70>
          <img src="@asset('images/logo-primary.png')" width="250" class="has-margin-left-40 logo" alt="Logo Amerinode">
        </a>
        <div class="navbar-end">
          <div class="navbar-item">
            <a data-gravity href="https://telefonica.amerinode.net/Account/Login.aspx" class="button is-dark">
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
          <p data-inertia data-inertia-reveal data-inertia-delay="200"
            class="has-text-weight-normal subtitle has-text-dark is-italic has-margin-bottom-40">Portafolio de Servicios
          </p>
          <h1 class="title is-1 has-text-weight-normal has-text-dark">
            <div data-inertia data-inertia-reveal><b>Construyendo</b> el camino</div>
            <div data-inertia data-inertia-reveal data-inertia-delay="100">hacia la <b>rentabilidad</b>.</div>
          </h1>
        </div>
      </div>
    </div>
  </div>
  <div data-inertia data-inertia-reveal class="hero-foot has-background-light column is-10">
    <div class="container is-fluid has-padding-left-80 has-padding-bottom-40">
      <div class="column is-8">
        <div class="columns has-margin-top-40">
          <div data-inertia data-inertia-reveal data-inertia-delay="300" class="column is-3 is-flex">
            <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                data-feather="monitor"></i></span>
            <span class="has-margin-left-20">Gestión de Servicios</span>
          </div>
          <div data-inertia data-inertia-reveal data-inertia-delay="400" class="column is-3 is-flex">
            <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                data-feather="cpu"></i></span>
            <span class="has-margin-left-20">Gestión de Repuestos</span>
          </div>
          <div data-inertia data-inertia-reveal data-inertia-delay="500" class="column is-3 is-flex">
            <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                data-feather="tool"></i></span>
            <span class="has-margin-left-20">Remanufactura</span>
          </div>
          <div data-inertia data-inertia-reveal data-inertia-delay="600" class="column is-3 is-flex">
            <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                data-feather="target"></i></span>
            <span class="has-margin-left-20">Socios Estratégicos</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="hero is-fullheight is-dark is-relative">
  <div class="is-overlay is-hidden-touch">
    <div class="column is-5 is-parallax-contain"
      style="background: url(@asset('images/gestion-de-servicio.jpg')); height: 100vh">
    </div>
  </div>
  <div class="hero-body">
    <div class="container is-fluid">
      <div class="columns level">
        <div class="column is-5">
          <div class="image is-1by1 is-parallax-contain is-hidden-desktop"
            style="background: url(@asset('images/gestion-de-servicio.jpg'))"></div>
        </div>
        <div class="column is-6 is-offset-1">
          <h2 data-inertia data-inertia-reveal class="title is-1 has-text-weight-normal">Gestión de <b
              class="has-text-primary">Servicios.</b></h2>
          <p data-inertia data-inertia-reveal class="subtitle has-margin-top-40">Basados en normas <b>ITIL</b> y formado
            por un equipo humano de <b>basta experiencia</b>, brindamos soluciones analíticas de calidad y costo
            eficiente.</p>
          <div data-inertia data-inertia-reveal class="columns has-margin-top-40">
            <div data-inertia data-inertia-reveal data-inertia-delay="200" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="mouse-pointer"></i></span>
              <span class="has-margin-left-20">Soporte técnico Especializado</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="300" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="help-circle"></i></span>
              <span class="has-margin-left-20">Consultoría Especializada</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="400" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="coffee"></i></span>
              <span class="has-margin-left-20">Ingeniería y Planeación</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="500" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="users"></i></span>
              <span class="has-margin-left-20">Operación Asistida</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-foot has-padding-bottom-40">
    <div class="column is-6 is-offset-6">
      <div class="buttons">

        <a href="/portafolio-de-servicios/gestion-de-servicios"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-primary has-padding-20">
          <span class="icon"><i data-feather="plus"></i></span>
          <span>Saber más</span>
        </a>

        <a href="/contactenos"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-light has-padding-20">
          <span class="icon"><i data-feather="disc"></i></span>
          <span>Contacto</span>
        </a>

      </div>
    </div>
  </div>
</div>

<div class="hero is-fullheight is-primary is-relative">
  <div class="is-overlay is-hidden-touch">
    <div class="column is-5 is-parallax-contain"
      style="background: url(@asset('images/reciclaje.jpg')); height: 100vh">
    </div>
  </div>
  <div class="hero-body">
    <div class="container is-fluid">
      <div class="columns level">
        <div class="column is-5">
          <div class="image is-1by1 is-parallax-contain is-hidden-desktop"
            style="background: url(@asset('images/reciclaje.jpg'))"></div>
        </div>
        <div class="column is-6 is-offset-1">
          <h2 data-inertia data-inertia-reveal class="title is-1 has-text-weight-normal">Gestión de <b
              class="has-text-dark">Repuestos.</b></h2>
          <p data-inertia data-inertia-reveal class="subtitle has-margin-top-40">
            Proveemos una gama completa de soluciones personalizadas a la <b>recuperación y reposición de activos</b>, con procesos simples, herramientas de control y analíticas.
          </p>
          <div data-inertia data-inertia-reveal class="columns has-margin-top-40">
            <div data-inertia data-inertia-reveal data-inertia-delay="200" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="hard-drive"></i></span>
              <span class="has-margin-left-20">Spare Parts Management</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="300" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="rotate-cw"></i></span>
              <span class="has-margin-left-20">Marketplace y Reciclaje</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="400" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="tool"></i></span>
              <span class="has-margin-left-20">Laboratorio de reparación y R&D</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="500" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="layout"></i></span>
              <span class="has-margin-left-20"><b>Quantum</b> Web Tool</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-foot has-padding-bottom-40">
    <div class="column is-6 is-offset-6">
      <div class="buttons">

        <a href="/portafolio-de-servicios/gestion-de-repuestos"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-dark has-padding-20">
          <span class="icon"><i data-feather="plus"></i></span>
          <span>Saber más</span>
        </a>

        <a href="/contactenos"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-light has-padding-20">
          <span class="icon"><i data-feather="disc"></i></span>
          <span>Contacto</span>
        </a>

      </div>
    </div>
  </div>
</div>

<div class="hero is-fullheight is-light is-relative">
  <div class="is-overlay is-hidden-touch">
    <div class="column is-5 is-parallax-contain"
      style="background: url(@asset('images/testing.jpg')); height: 100vh">
    </div>
  </div>
  <div class="hero-body">
    <div class="container is-fluid">
      <div class="columns level">
        <div class="column is-5">
          <div class="image is-1by1 is-parallax-contain is-hidden-desktop"
            style="background: url(@asset('images/testing.jpg'))"></div>
        </div>
        <div class="column is-6 is-offset-1">
          <h2 data-inertia data-inertia-reveal class="title is-1 has-text-weight-normal has-text-dark">Remanufactura de <b
              class="has-text-primary">Terminales.</b></h2>
          <p data-inertia data-inertia-reveal class="subtitle has-margin-top-40">
          Aportando a la economía circular usamos herramientas y automatismos <b>para la reutilización integral de terminales B2B/B2C</b> fijos y moviles, optimizando tiempos de compra y reposición.
          </p>
          <div data-inertia data-inertia-reveal class="columns has-margin-top-40">
            <div data-inertia data-inertia-reveal data-inertia-delay="200" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="tool"></i></span>
              <span class="has-margin-left-20">Reparación electrónica</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="300" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="search"></i></span>
              <span class="has-margin-left-20">Jiga-testeo automatizado</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="400" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="package"></i></span>
              <span class="has-margin-left-20">Limpieza, pintura y kitting</span>
            </div>
            <div data-inertia data-inertia-reveal data-inertia-delay="500" class="column is-3 is-flex">
              <span data-cursor="-icon" data-cursor-stick data-gravity data-cursor-text="." class="icon"><i
                  data-feather="layout"></i></span>
              <span class="has-margin-left-20"><b>SGT</b> Web Tool</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-foot has-padding-bottom-40">
    <div class="column is-6 is-offset-6">
      <div class="buttons">

        <a href="/portafolio-de-servicios/remanufactura"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-primary has-padding-20">
          <span class="icon"><i data-feather="plus"></i></span>
          <span>Saber más</span>
        </a>

        <a href="/contactenos"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-light has-padding-20">
          <span class="icon"><i data-feather="disc"></i></span>
          <span>Contacto</span>
        </a>

      </div>
    </div>
  </div>
</div>

<div>
  @while(have_posts()) @php(the_post())
  @includeFirst(['partials.content-page', 'partials.content'])
  @endwhile
</div>

<div class="hero is-fullheight is-dark is-relative">
  <div class="is-overlay columns is-mobile">
    <div class="column is-3 is-offset-9 has-background-white" style="height: 100%"></div>
  </div>
  <div class="hero-body">
    <div class="container is-fluid">
      <div class="columns level">
        <div class="column is-6">
          <div data-inertia data-inertia-reveal class="title is-1 has-text-weight-normal">
            <b>Nuestra</b> Compañía
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-foot">
    <div class="container is-fluid has-padding-left-80 has-padding-bottom-15">
      <div class="buttons">
        <a href="/nuestra-compania"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="100"
          data-inertia-id="continue" data-direction="right"
          class="button is-dark is-inverted has-padding-20">
          <span class="icon"><i data-feather="arrow-right"></i></span>
          <span>¿Quienes somos en Amerinode?</span>
        </a>
        <a href="/contactenos"
          data-gravity
          data-inertia
          data-inertia-reveal
          data-inertia-delay="200"
          class="button is-transparent is-light has-padding-20">
          <span class="icon"><i data-feather="disc"></i></span>
          <span>Contacto</span>
        </a>
      </div>
    </div>
  </div>
</div>



@endsection

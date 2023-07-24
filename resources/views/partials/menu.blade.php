<div id="full_menu" class="full-menu is-overlay is-disabled" style="z-index: 9">
  <div class="hero is-fullheight is-dark">
    <div class="is-overlay" style="mix-blend-mode: lighten">
      <div class="fibra-optica column is-3 is-paddingless is-offset-9">
        <div style="height: 100vh; width: 100%; background: url('@asset('images/fibra-optica.jpg')'); background-size: cover; background-position:center;">
        </div>
      </div>
    </div>
    <div class="hero-body">
      <div class="container">
        <div class="columns">
          <div class="column is-5">
            <div class="revealer" data-inertia data-inertia-reveal>
              @if (has_nav_menu('primary_navigation'))
                {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'menu-column title is-2 has-text-weight-normal', 'echo' => false]) !!}
              @endif
            </div>
            <ul class="documents has-margin-top-70">
              <li data-inertia data-inertia-reveal data-inertia-delay="800"><a data-cursor-text="+" href="/politica-privacidad" class="title is-5 has-text-weight-normal">Política de Privacidad</a></li>
              <li data-inertia data-inertia-reveal data-inertia-delay="900" class="has-margin-top-10"><a data-cursor-text="+" href="/politica-calidad" class="title is-5 has-text-weight-normal">Política de Calidad</a></li>
            </ul>
          </div>
          <div class="column is-4">
            <div data-inertia data-inertia-reveal data-inertia-delay="100"  class="title is-7">Iniciativas Amerinode</div>
            <a  data-inertia data-inertia-reveal data-inertia-delay="200" href="/como-llevamos-la-tecnologia-a-las-comunidades" data-cursor="-inverse" data-cursor-text="Leer" class="title is-4 has-text-weight-light is-block">Cómo llevamos tecnología a las comunidades</a>
            <a  data-inertia data-inertia-reveal data-inertia-delay="300"  href="/blog" data-cursor-text="Ir al Blog" class="is-size-7 is-block">- Y decenas de más historias de éxito en nuestro blog. </a>
            <div data-inertia data-inertia-reveal data-inertia-delay="400"  data-inertia data-inertia-reveal data-inertia-delay="500"  class="title is-7 has-margin-bottom-10 has-margin-top-105">Visítenos</div>
            <a  data-inertia data-inertia-reveal data-inertia-delay="500" class="is-block" href="https://maps.google.es/maps?daddr=CARRERA%207%2071%2052%20TO%20A%20OF%20504,BOGOTA%20-%20BOGOTA,%20Colombia" target="_blank">Carrera 7 # 71 - 52 Torre B Piso 15, Bogota</a>
            <div data-inertia data-inertia-reveal data-inertia-delay="600"  class="title is-7 has-margin-bottom-10 has-margin-top-30 ">O Escríbanos</div>
            <a class="is-block" data-inertia data-inertia-reveal data-inertia-delay="700"  href="mailto:info@latam.amerinode.com">info@latam.amerinode.com</a>
          </div>
        </div>
      </div>
    </div>
    <div class="hero-footer">
      <div class="hero-body">
        <div class="container">
          <a data-gravity href="https://www.linkedin.com/company/amerinode/" class="button is-white is-outlined"><span class="icon"><i data-feather="linkedin"></i></span></a>
        </div>
      </div>
    </div>
  </div>
</div>
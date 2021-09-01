<section class="hero is-fullheight has-background-light">
  <div class="hero-body">
    <div class="container">
      <div class="card-content has-text-centered has-margin-top-100 has-margin-bottom-100" data-aos="fade-up">
        <h2 class="title is-2 has-text-uppercase">Titulo h2</h2>
        <p class="has-margin-top-30">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quae, dolorem cupiditate quas, deleniti doloribus aperiam ullam dignissimos eos odio saepe dolores aliquam, voluptatem fugiat repellendus soluta excepturi molestias tempora expedita?</p>
      </div>
      
      <div class="slider is-full-width" data-aos="fade-up">
        @query([
          'post_type' => 'post'
        ])
        @posts
        <div class="column is-inline-flex">
          @include('components.card')
        </div>
        @endposts
      </div>
    </div>
  </div>
</section>

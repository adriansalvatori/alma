<article class="box @php(post_class())">
  <header class="media">
    <div class="media-content">
      <h2 class="title is-4">
        <a href="{{ get_permalink() }}" class="has-text-dark">
          {!! $title !!}
        </a>
      </h2>
      @include('partials.entry-meta')
    </div>
  </header>

  <div class="content">
    @php(the_excerpt())
  </div>
</article>


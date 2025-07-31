<article class="box">
  <header class="media">
    <div class="media-content">
      <p class="title is-4">
        <a href="{{ get_permalink() }}" class="has-text-dark">
          {!! $title !!}
        </a>
      </p>
    </div>
    <div class="media-right">
      @includeWhen(get_post_type() === 'post', 'partials.entry-meta')
    </div>
  </header>
  <div class="content">
    @php(the_excerpt())
  </div>
</article>


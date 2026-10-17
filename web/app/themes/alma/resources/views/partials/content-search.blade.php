{{-- Search result: linked title, meta for posts, and excerpt. --}}
<article @php(post_class("space-y-2"))>
  <header>
    <h2 class="entry-title text-title font-bold text-zinc-900 dark:text-white">
      <a href="{{ get_permalink() }}">
        {!! $title !!}
      </a>
    </h2>

    @includeWhen(get_post_type() === 'post', 'partials.entry-meta')
  </header>

  <div class="entry-summary">
    @php(the_excerpt())
  </div>
</article>

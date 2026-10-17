{{-- Post summary: linked title, meta and excerpt in an archive list. --}}
<article @php(post_class("space-y-2"))>
  <header>
    <h2 class="entry-title text-title font-bold text-zinc-900 dark:text-white">
      <a href="{{ get_permalink() }}">
        {!! $title !!}
      </a>
    </h2>

    @include('partials.entry-meta')
  </header>

  <div class="entry-summary">
    @php(the_excerpt())
  </div>
</article>

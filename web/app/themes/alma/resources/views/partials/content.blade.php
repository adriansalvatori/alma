<article class="bg-white rounded-lg shadow-md overflow-hidden @php(post_class())">
  <header class="p-4 border-b">
    <h2 class="text-xl font-semibold">
      <a href="{{ get_permalink() }}" class="text-gray-900 hover:text-gray-700">
        {!! $title !!}
      </a>
    </h2>

    @include('partials.entry-meta')
  </header>

  <div class="px-4 py-6">
    @php(the_excerpt())
  </div>
</article>


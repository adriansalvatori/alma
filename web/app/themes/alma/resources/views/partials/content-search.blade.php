<article class="flex flex-col bg-white rounded-lg shadow-md overflow-hidden">
  <header class="flex items-center justify-between p-4 border-b">
    <h2 class="text-lg font-semibold">
      <a href="{{ get_permalink() }}" class="text-gray-900 hover:text-gray-700">
        {!! $title !!}
      </a>
    </h2>

    @includeWhen(get_post_type() === 'post', 'partials.entry-meta')
  </header>

  <div class="px-4 py-6">
    @php(the_excerpt())
  </div>
</article>


<article class="h-entry p-6 bg-white rounded-lg shadow-md overflow-hidden">
  <header class="flex items-center justify-between mb-4 border-b">
    <h1 class="p-name text-3xl font-semibold">
      {!! $title !!}
    </h1>

    @include('partials.entry-meta')
  </header>

  <div class="e-content prose prose-sm lg:prose lg:prose-lg mx-auto">
    @php(the_content())
  </div>

  @if ($pagination())
    <footer class="mt-8">
      <nav class="page-nav flex items-center justify-between" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </footer>
  @endif

  @php(comments_template())
</article>


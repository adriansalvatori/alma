<article class="card is-shadowless">
  <header class="card-header">
    <h1 class="card-header-title p-name is-size-3">{{ $title }}</h1>

    @include('partials.entry-meta')
  </header>

  <div class="card-content e-content prose prose-sm lg:prose lg:prose-lg">
    @php(the_content())
  </div>

  @if ($pagination())
    <footer class="card-footer">
      <nav class="pagination is-centered" role="navigation" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </footer>
  @endif

  @php(comments_template())
</article>


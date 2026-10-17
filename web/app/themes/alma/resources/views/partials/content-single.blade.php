{{-- Single post: title, meta, reading time, content and comments in a readable column. --}}
<article @php(post_class('h-entry container-page py-12'))>
  <div class="mx-auto max-w-3xl">
    <header class="mb-8 space-y-3">
      <h1 class="p-name text-heading font-bold tracking-tight text-zinc-900 dark:text-white">{!! $title !!}</h1>
      @include('partials.entry-meta')
    </header>

    <div class="e-content prose-content">
      @php(the_content())
    </div>

    @if ($pagination())
      <footer>
        <nav class="page-nav" aria-label="{{ __('Page', 'alma') }}">
          {!! $pagination !!}
        </nav>
      </footer>
    @endif

    @php(comments_template())
  </div>
</article>

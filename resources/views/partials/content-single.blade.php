
@include('partials.page-header')
  <div class="is-relative">
    <div class="container py-6">
      <h1 class="title is-1 has-margin-bottom-40">@title</h1>
      <article @php(post_class())>
        <div class="entry-content">
          @php(the_content())
        </div>
      
        <footer>
          {!! wp_link_pages(['echo' => 0, 'before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']) !!}
        </footer>
      
        @php(comments_template())
      </article>
    </div>
  </div>
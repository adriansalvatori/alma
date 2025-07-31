<div class="content">
  @php(the_content())
</div>

@if ($pagination())
  <nav class="pagination is-centered" role="navigation" aria-label="pagination">
    {!! $pagination !!}
  </nav>
@endif


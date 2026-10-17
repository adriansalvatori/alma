{{-- Page content: blocks in a constrained flow (alignfull blocks break out to the edges). --}}
<div class="entry-content">
  @php(the_content())
</div>

@if ($pagination())
  <nav class="page-nav container-page" aria-label="{{ __('Page', 'alma') }}">
    {!! $pagination !!}
  </nav>
@endif

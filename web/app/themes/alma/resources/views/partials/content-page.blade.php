<div class="prose prose-sm lg:prose lg:prose-lg mx-auto">
  @php(the_content())
</div>

@if ($pagination())
  <nav class="flex items-center justify-between py-4" aria-label="Page">
    {!! $pagination !!}
  </nav>
@endif


@if (! post_password_required())
  <section id="comments" class="comments">
    @if ($responses())
      <h2 class="title is-4">
        {!! $title !!}
      </h2>

      <ol class="comment-list ml-5">
        {!! $responses !!}
      </ol>

      @if ($paginated())
        <nav aria-label="Comment" class="mt-4">
          <ul class="pagination is-flex is-justify-content-space-between">
            @if ($previous())
              <li class="pagination-previous">
                {!! $previous !!}
              </li>
            @endif

            @if ($next())
              <li class="pagination-next">
                {!! $next !!}
              </li>
            @endif
          </ul>
        </nav>
      @endif
    @endif

    @if ($closed())
      <x-alert type="warning">
        {!! __('Comments are closed.', 'alma') !!}
      </x-alert>
    @endif

    @php(comment_form())
  </section>
@endif


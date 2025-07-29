@if (! post_password_required())
  <section id="comments" class="comments space-y-6">
    @if ($responses())
      <h2 class="text-2xl font-bold">
        {!! $title !!}
      </h2>

      <ol class="comment-list list-decimal pl-6 space-y-4">
        {!! $responses !!}
      </ol>

      @if ($paginated())
        <nav aria-label="Comment" class="mt-4">
          <ul class="pager flex justify-between">
            @if ($previous())
              <li class="previous">
                {!! $previous !!}
              </li>
            @endif

            @if ($next())
              <li class="next">
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


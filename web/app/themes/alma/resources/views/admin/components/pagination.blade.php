{{-- "Page n of m" with Previous / Next, for a LengthAwarePaginator built on wp-admin's `paged` (see LeadsAdmin::paginate()). --}}
@props(['paginator'])

<div {{ $attributes->class(['alma-pagination']) }}>
  <div>{!! sprintf(esc_html__('Page %1$s of %2$s', 'alma'), '<strong>'.$paginator->currentPage().'</strong>', '<strong>'.max(1, $paginator->lastPage()).'</strong>') !!}</div>
  <div class="alma-pagination__links">
    @if ($paginator->previousPageUrl())
      <a href="{{ $paginator->previousPageUrl() }}" class="alma-btn alma-btn--sm">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        {{ __('Previous', 'alma') }}
      </a>
    @endif
    @if ($paginator->nextPageUrl())
      <a href="{{ $paginator->nextPageUrl() }}" class="alma-btn alma-btn--sm">
        {{ __('Next', 'alma') }}
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </a>
    @endif
  </div>
</div>

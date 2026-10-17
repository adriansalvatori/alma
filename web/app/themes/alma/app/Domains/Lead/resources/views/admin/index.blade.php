{{-- wp-admin → Leads: the list of leads with search, status views, form filter and CSV export. --}}
<div class="wrap">
  <h1 class="wp-heading-inline">{{ __('Leads', 'alma') }}</h1>
  <a href="{{ $exportUrl }}" class="page-title-action">{{ __('Export CSV', 'alma') }}</a>
  <hr class="wp-header-end">

  @php($table->views())

  <form method="get">
    <input type="hidden" name="page" value="{{ \App\Domains\Lead\Admin\LeadsAdmin::PAGE }}">
    @if (! empty($filters['status']))
      <input type="hidden" name="status" value="{{ $filters['status'] }}">
    @endif
    @php($table->search_box(__('Search leads', 'alma'), 'alma-leads'))
    @php($table->display())
  </form>
</div>

{{-- wp-admin → Leads: the list of leads with search, status views, form filter and CSV export. --}}
@php($admin = \App\Domains\Lead\Admin\LeadsAdmin::class)

<x-admin::page :title="__('Leads', 'alma')" :description="__('Every enquiry the site has captured, newest first. Open one for its attribution and delivery log.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="$admin::url([], $admin::SETTINGS_PAGE)" variant="ghost">{{ __('Settings', 'alma') }}</x-admin::button>
    <x-admin::button :href="$exportUrl">{{ __('Export CSV', 'alma') }}</x-admin::button>
  </x-slot:actions>

  @php($table->views())

  <form method="get">
    <input type="hidden" name="page" value="{{ $admin::PAGE }}">
    @if (! empty($filters['status']))
      <input type="hidden" name="status" value="{{ $filters['status'] }}">
    @endif
    @php($table->search_box(__('Search leads', 'alma'), 'alma-leads'))
    @php($table->display())
  </form>
</x-admin::page>

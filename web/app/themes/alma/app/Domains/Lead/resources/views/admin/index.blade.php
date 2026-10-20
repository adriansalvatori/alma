{{-- wp-admin → Leads: KPI cards that filter, the filter toolbar, every lead newest first, CSV export and the retention purge. --}}
@php
  $admin = \App\Domains\Lead\Admin\LeadsAdmin::class;
  $avatar = \App\Domains\Lead\Services\LeadAvatar::class;
  $status = $filters['status'] ?? '';
  $filtered = $filters !== [];
  $icons = [
    'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
    'trending' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
    'award' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
    'activity' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
  ];
  $arrow = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
@endphp

<x-admin::page :title="__('Lead management & submissions', 'alma')" :description="__('Every enquiry the site has captured, where it came from, and what each integration did with it.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="$exportUrl">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
      {{ $filtered ? __('Export filtered CSV', 'alma') : __('Export CSV', 'alma') }}
    </x-admin::button>
    <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-inline-form" onsubmit="return confirm(@js(sprintf(__('Delete every lead older than %d days, with its activity? This cannot be undone.', 'alma'), $retentionDays)));">
      <input type="hidden" name="action" value="alma_leads_purge">
      {!! wp_nonce_field('alma_leads_purge', '_wpnonce', true, false) !!}
      <x-admin::button type="submit" variant="danger">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
        {{ sprintf(__('Run retention purge (%d+ days)', 'alma'), $retentionDays) }}
      </x-admin::button>
    </form>
  </x-slot:actions>

  @if ($purged !== null)
    <div class="notice notice-success is-dismissible"><p>{{ sprintf(_n('Retention purge complete: %1$d lead older than %2$d days was deleted.', 'Retention purge complete: %1$d leads older than %2$d days were deleted.', $purged, 'alma'), $purged, $retentionDays) }}</p></div>
  @endif
  @if ($deleted)
    <div class="notice notice-info is-dismissible"><p>{{ __('The lead and its activity were permanently deleted.', 'alma') }}</p></div>
  @endif

  <x-admin::tabs :tabs="$admin::tabs()" active="leads" />

  <div class="alma-stats-grid">
    <x-admin::stat :label="__('Total submissions', 'alma')" :value="number_format_i18n($kpis['total'])" :hint="__('Every captured lead, spam aside', 'alma')" :href="$admin::url()" :active="! $filtered">
      <x-slot:icon>{!! $icons['users'] !!}</x-slot:icon>
    </x-admin::stat>
    <x-admin::stat :label="__('Awaiting reply', 'alma')" :value="number_format_i18n($kpis['new'])" :hint="__('New, nobody has answered yet', 'alma')" :href="$admin::url(['status' => 'new'])" :active="$status === 'new'">
      <x-slot:icon>{!! $icons['inbox'] !!}</x-slot:icon>
    </x-admin::stat>
    <x-admin::stat :label="__('Qualified', 'alma')" :value="number_format_i18n($kpis['qualified'])" :hint="__('A real opportunity, in progress', 'alma')" :href="$admin::url(['status' => 'qualified'])" :active="$status === 'qualified'">
      <x-slot:icon>{!! $icons['trending'] !!}</x-slot:icon>
    </x-admin::stat>
    <x-admin::stat :label="__('Won', 'alma')" :value="number_format_i18n($kpis['won'])" :hint="$kpis['total'] > 0 ? sprintf(__('%s%% of submissions', 'alma'), number_format_i18n($kpis['won'] / $kpis['total'] * 100, 1)) : __('Closed as customers', 'alma')" :href="$admin::url(['status' => 'won'])" :active="$status === 'won'">
      <x-slot:icon>{!! $icons['award'] !!}</x-slot:icon>
    </x-admin::stat>
    <x-admin::stat :label="__('Audit log events', 'alma')" :value="number_format_i18n($kpis['logs'])" :hint="__('Dispatches and outcomes, logged', 'alma')" :href="$admin::url([], $admin::ACTIVITY_PAGE)">
      <x-slot:icon>{!! $icons['activity'] !!}</x-slot:icon>
    </x-admin::stat>
  </div>

  <div class="alma-filter-bar">
    <form method="get" action="{{ admin_url('admin.php') }}" class="alma-filter-form">
      <input type="hidden" name="page" value="{{ $admin::PAGE }}">

      <div class="alma-search">
        <svg class="alma-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="s" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search name, email, phone, company…', 'alma') }}" class="alma-search__input" aria-label="{{ __('Search leads', 'alma') }}">
      </div>

      <select name="status" class="alma-select" aria-label="{{ __('Status', 'alma') }}">
        <option value="">{{ __('All statuses', 'alma') }}</option>
        @foreach ($statuses as $option)
          <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
        @endforeach
      </select>

      @if (count($forms) > 1)
        <select name="form" class="alma-select" aria-label="{{ __('Form', 'alma') }}">
          <option value="">{{ __('All forms', 'alma') }}</option>
          @foreach ($forms as $form)
            <option value="{{ $form }}" @selected(($filters['form'] ?? '') === $form)>{{ $form }}</option>
          @endforeach
        </select>
      @endif

      @if ($sources !== [])
        <select name="source" class="alma-select" aria-label="{{ __('Channel', 'alma') }}">
          <option value="">{{ __('All channels', 'alma') }}</option>
          @foreach ($sources as $source)
            <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source === \App\Domains\Lead\Services\LeadQuery::DIRECT ? __('Direct', 'alma') : $source }}</option>
          @endforeach
        </select>
      @endif

      <select name="date_range" class="alma-select" aria-label="{{ __('Date', 'alma') }}">
        <option value="">{{ __('All dates', 'alma') }}</option>
        @foreach ($dateRanges as $slug => $label)
          <option value="{{ $slug }}" @selected(($filters['date_range'] ?? '') === $slug)>{{ $label }}</option>
        @endforeach
      </select>

      <x-admin::button type="submit" variant="primary">{{ __('Filter', 'alma') }}</x-admin::button>
      @if ($filtered)
        <x-admin::button :href="$admin::url()">{{ __('Reset', 'alma') }}</x-admin::button>
      @endif
    </form>

    <div class="alma-count-badge">
      {!! sprintf(esc_html__('Showing %1$s of %2$s leads', 'alma'), '<strong>'.count($leads->items()).'</strong>', '<strong>'.number_format_i18n($leads->total()).'</strong>') !!}
    </div>
  </div>

  <div class="alma-table-container">
    <table class="alma-data-table">
      <thead>
        <tr>
          <th>{{ __('Date / age', 'alma') }}</th>
          <th>{{ __('Contact & company', 'alma') }}</th>
          <th>{{ __('Form', 'alma') }}</th>
          <th>{{ __('Status', 'alma') }}</th>
          <th class="alma-col-attr">{{ __('Attribution', 'alma') }}</th>
          <th>{{ __('Audit log', 'alma') }}</th>
          <th style="text-align: right;">{{ __('Action', 'alma') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($leads as $lead)
          @php
            $url = $admin::url(['lead' => $lead->id]);
            $first = is_array($lead->attribution['first'] ?? null) ? $lead->attribution['first'] : [];
          @endphp
          <tr>
            <td>
              <div class="alma-data-table__date">{{ $lead->created_at?->format('M j, Y') }}</div>
              <div class="alma-data-table__age" title="{{ $lead->created_at?->toDateTimeString() }}">{{ $lead->created_at?->diffForHumans() }}</div>
            </td>
            <td>
              <div class="alma-contact">
                <x-admin::avatar :initials="$avatar::initials($lead->name, $lead->email)" :tint="$avatar::tint($lead->email, $lead->name)" :logo="$avatar::logoUrl($lead->email)" :title="$avatar::domain($lead->email)" />
                <div class="alma-contact__info">
                  <a href="{{ $url }}" class="alma-contact__name">{{ $lead->name !== '' ? $lead->name : __('(no name)', 'alma') }}</a>
                  <a href="mailto:{{ $lead->email }}" class="alma-contact__email" title="{{ $lead->email }}">{{ $lead->email }}</a>
                  @if ($lead->phone || $lead->company)
                    <div class="alma-contact__sub">
                      @if ($lead->phone)
                        <span>
                          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                          {{ $lead->phone }}
                        </span>
                      @endif
                      @if ($lead->company)
                        <span class="alma-contact__company">
                          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>
                          {{ $lead->company }}
                        </span>
                      @endif
                    </div>
                  @endif
                </div>
              </div>
            </td>
            <td><span class="alma-pill">{{ $lead->form_key }}</span></td>
            <td><x-admin::badge :tone="$lead->status->tone()" dot>{{ $lead->status->label() }}</x-admin::badge></td>
            <td class="alma-col-attr">
              <div class="alma-attr">
                @if (! empty($first['source']) || ! empty($first['campaign']))
                  <span class="alma-attr__source" title="{{ $first['source'] ?? '' }}">{{ $first['source'] ?? __('direct', 'alma') }}{{ ! empty($first['medium']) ? ' / '.$first['medium'] : '' }}</span>
                  @if (! empty($first['campaign']))
                    <span class="alma-attr__campaign" title="{{ $first['campaign'] }}">{{ sprintf(__('cmp: %s', 'alma'), $first['campaign']) }}</span>
                  @endif
                @else
                  <span class="alma-faint">{{ __('Direct', 'alma') }}</span>
                @endif
              </div>
            </td>
            <td>
              <a href="{{ $url }}" class="alma-audit-pill">{{ sprintf(_n('%d event', '%d events', (int) $lead->activity_count, 'alma'), (int) $lead->activity_count) }} {!! $arrow !!}</a>
            </td>
            <td style="text-align: right;">
              <x-admin::button :href="$url" size="sm">{{ __('View details', 'alma') }} {!! $arrow !!}</x-admin::button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="alma-data-table__empty">
              {{ $filtered ? __('No submissions match these filters.', 'alma') : __('No leads yet. They appear here as soon as someone submits a form.', 'alma') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <x-admin::pagination :paginator="$leads" />
</x-admin::page>

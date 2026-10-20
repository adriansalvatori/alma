{{-- wp-admin → Leads → Activity log: the audit trail across every lead, newest first, filterable by event, actor and outcome. --}}
@php
  $admin = \App\Domains\Lead\Admin\LeadsAdmin::class;
  $avatar = \App\Domains\Lead\Services\LeadAvatar::class;
  $activity = \App\Domains\Lead\Models\LeadActivity::class;
  $outcomeTone = fn (string $outcome) => match ($outcome) {
    $activity::SUCCEEDED => 'success',
    $activity::FAILED => 'danger',
    $activity::DISPATCHED => 'info',
    default => 'neutral',
  };
@endphp

<x-admin::page :title="__('Live activity & audit log', 'alma')" :description="__('Every capture is logged as a dispatch, and every integration (notification email, CRM webhook, a status change) logs its own outcome.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="$admin::url()">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      {{ __('Back to all leads', 'alma') }}
    </x-admin::button>
  </x-slot:actions>

  <x-admin::tabs :tabs="$admin::tabs()" active="activity" />

  <div class="alma-filter-bar">
    <form method="get" action="{{ admin_url('admin.php') }}" class="alma-filter-form">
      <input type="hidden" name="page" value="{{ $admin::ACTIVITY_PAGE }}">

      <div class="alma-search">
        <svg class="alma-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="s" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search event, actor, email…', 'alma') }}" class="alma-search__input" aria-label="{{ __('Search the log', 'alma') }}">
      </div>

      <select name="event" class="alma-select" aria-label="{{ __('Event', 'alma') }}">
        <option value="">{{ __('All events', 'alma') }}</option>
        @foreach ($events as $event)
          <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ $event }}</option>
        @endforeach
      </select>

      <select name="actor" class="alma-select" aria-label="{{ __('Actor', 'alma') }}">
        <option value="">{{ __('All actors', 'alma') }}</option>
        @foreach ($actors as $actor)
          <option value="{{ $actor }}" @selected(($filters['actor'] ?? '') === $actor)>{{ $actor }}</option>
        @endforeach
      </select>

      <select name="outcome" class="alma-select" aria-label="{{ __('Outcome', 'alma') }}">
        <option value="">{{ __('All outcomes', 'alma') }}</option>
        @foreach ($outcomes as $outcome)
          <option value="{{ $outcome }}" @selected(($filters['outcome'] ?? '') === $outcome)>{{ ucfirst($outcome) }}</option>
        @endforeach
      </select>

      <x-admin::button type="submit" variant="primary">{{ __('Filter log', 'alma') }}</x-admin::button>
      @if ($filters !== [])
        <x-admin::button :href="$admin::url([], $admin::ACTIVITY_PAGE)">{{ __('Reset', 'alma') }}</x-admin::button>
      @endif
    </form>

    <div class="alma-count-badge">
      {!! sprintf(esc_html__('Total: %s audit events', 'alma'), '<strong>'.number_format_i18n($logs->total()).'</strong>') !!}
    </div>
  </div>

  <div class="alma-table-container">
    <table class="alma-data-table">
      <thead>
        <tr>
          <th>{{ __('Timestamp', 'alma') }}</th>
          <th>{{ __('Lead', 'alma') }}</th>
          <th>{{ __('Event', 'alma') }}</th>
          <th>{{ __('Actor', 'alma') }}</th>
          <th>{{ __('Outcome', 'alma') }}</th>
          <th>{{ __('Payload', 'alma') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($logs as $log)
          <tr>
            <td>
              <div class="alma-data-table__date">{{ $log->created_at?->format('M j, Y') }}</div>
              <div class="alma-data-table__age" title="{{ $log->created_at?->toDateTimeString() }}">{{ $log->created_at?->format('H:i:s') }} ({{ $log->created_at?->diffForHumans() }})</div>
            </td>
            <td>
              @if ($log->lead)
                <div class="alma-contact">
                  <x-admin::avatar :initials="$avatar::initials($log->lead->name, $log->lead->email)" :tint="$avatar::tint($log->lead->email, $log->lead->name)" :logo="$avatar::logoUrl($log->lead->email)" :title="$avatar::domain($log->lead->email)" />
                  <div class="alma-contact__info">
                    <a href="{{ $admin::url(['lead' => $log->lead_id]) }}" class="alma-contact__name">{{ $log->lead->name !== '' ? $log->lead->name : $log->lead->email }}</a>
                    <span class="alma-faint">{{ $log->lead->email }}</span>
                  </div>
                </div>
              @else
                <span class="alma-faint">{{ sprintf(__('Lead #%d (deleted)', 'alma'), $log->lead_id) }}</span>
              @endif
            </td>
            <td style="max-width: 320px;"><code>{{ $log->event }}</code></td>
            <td><strong>{{ $log->actor }}</strong></td>
            <td><x-admin::badge :tone="$outcomeTone($log->outcome)" dot>{{ $log->outcome }}</x-admin::badge></td>
            <td>
              @if (! empty($log->detail))
                <details>
                  <summary>JSON</summary>
                  <div class="alma-json">{{ json_encode($log->detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</div>
                </details>
              @else
                <span class="alma-faint">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="alma-data-table__empty">{{ __('No audit events match these filters.', 'alma') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <x-admin::pagination :paginator="$logs" />
</x-admin::page>

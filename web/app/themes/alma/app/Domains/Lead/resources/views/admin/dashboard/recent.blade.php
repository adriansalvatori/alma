{{-- Dashboard → Recent captured leads: the latest six, their form and status, and a way into each. --}}
@if ($rows === [])
  <div class="alma-dash-empty">
    <p>{{ __('No captured leads yet. Every form submission lands here as it comes in.', 'alma') }}</p>
    <a href="{{ home_url('/') }}" target="_blank" rel="noopener" class="button button-secondary">{{ __('Open the site', 'alma') }} &rarr;</a>
  </div>
@else
  <div class="alma-dash-leads-list">
    <table class="alma-dash-table">
      <thead>
        <tr>
          <th>{{ __('Contact & company', 'alma') }}</th>
          <th>{{ __('Form', 'alma') }}</th>
          <th>{{ __('Status', 'alma') }}</th>
          <th>{{ __('Captured', 'alma') }}</th>
          <th style="text-align: right;">{{ __('Action', 'alma') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $row)
          <tr>
            <td>
              <div class="alma-dash-contact-cell">
                <div class="alma-dash-avatar" style="background: {{ $row['tint'][0] }} !important; color: {{ $row['tint'][1] }} !important;">{{ $row['initials'] }}</div>
                <div>
                  <div class="alma-dash-lead-name">{{ $row['name'] }}</div>
                  <div class="alma-dash-lead-sub">{{ $row['lead']->company ?: $row['lead']->email }}</div>
                </div>
              </div>
            </td>
            <td><span class="alma-badge-subtle">{{ $row['lead']->form_key }}</span></td>
            <td><span class="{{ $row['statusClass'] }}">{{ $row['lead']->status->label() }}</span></td>
            <td><span class="alma-dash-timestamp">{{ $row['timeAgo'] }}</span></td>
            <td style="text-align: right;"><a href="{{ $row['inspectUrl'] }}" class="alma-dash-btn-ghost">{{ __('Inspect', 'alma') }} &rarr;</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="alma-dash-card-footer">
      <span class="alma-dash-footer-meta">{{ sprintf(_n('Showing the latest %d submission', 'Showing the latest %d submissions', count($rows), 'alma'), count($rows)) }}</span>
      <a href="{{ \App\Domains\Lead\Admin\LeadsAdmin::url() }}" class="alma-dash-link">{{ sprintf(__('View all %s leads', 'alma'), number_format_i18n($total)) }} &rarr;</a>
    </div>
  </div>
@endif

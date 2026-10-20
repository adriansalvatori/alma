{{-- One health row: indicator, name, summary and status, with its evidence behind "Details". --}}
<div class="alma-dash-health-item alma-dash-health-item--expandable">
  <div class="alma-dash-health-top">
    <div class="alma-dash-health-left">
      <span class="{{ $row['style']['indicator'] }}"></span>
      <div>
        <div class="alma-dash-health-name">{{ $row['title'] }}</div>
        <div class="alma-dash-health-desc">{{ $row['desc'] }}</div>
      </div>
    </div>
    <span class="{{ $row['style']['badge'] }}">{{ $row['label'] }}</span>
  </div>
  @if (! empty($row['details']))
    <details class="alma-dash-health-details">
      <summary>
        <span class="dashicons dashicons-arrow-right-alt2 alma-dash-health-details-caret"></span>
        {{ __('Details', 'alma') }}
      </summary>
      <div class="alma-dash-health-details-body">
        @foreach ($row['details'] as $name => $value)
          <div>{{ ucfirst(str_replace('_', ' ', (string) $name)) }}: <code>{{ $value !== '' ? $value : '—' }}</code></div>
        @endforeach
      </div>
    </details>
  @endif
</div>

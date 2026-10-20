{{-- Dashboard → Traffic acquisition & attribution channels: each first-touch channel's share of the window's leads. --}}
<div class="alma-dash-channels-wrap">
  <div class="alma-dash-chart-header">
    <div>
      <span class="alma-dash-box-subtitle">{{ __('Attribution channel share', 'alma') }}</span>
      <div class="alma-dash-box-meta">{{ sprintf(__('Lead volume by first-touch source, last %d days', 'alma'), $windowDays) }}</div>
    </div>
    <span class="alma-dash-badge-subtle">{{ sprintf(_n('%d detected source', '%d detected sources', count($channels), 'alma'), count($channels)) }}</span>
  </div>

  @if ($channels !== [])
    <div class="alma-dash-stacked-bar">
      @foreach ($channels as $channel)
        <div class="alma-dash-stacked-seg" style="width: {{ max(3, $channel['pct']) }}%; background-color: {{ $channel['color'] }};" title="{{ $channel['source'] }}: {{ $channel['count'] }} ({{ $channel['pct'] }}%)"></div>
      @endforeach
    </div>

    <div class="alma-dash-channels-grid">
      @foreach ($channels as $channel)
        <div class="alma-dash-channel-item">
          <div class="alma-dash-channel-left">
            <span class="alma-dash-color-dot" style="background-color: {{ $channel['color'] }};"></span>
            <span class="alma-dash-channel-name">{{ $channel['source'] }}</span>
          </div>
          <div class="alma-dash-channel-right">
            <span class="alma-dash-channel-count">{{ number_format_i18n($channel['count']) }}</span>
            <span class="alma-dash-channel-pct">{{ $channel['pct'] }}%</span>
            <a href="{{ $channel['filterUrl'] }}" class="alma-dash-channel-link" title="{{ sprintf(__('Filter leads by %s', 'alma'), $channel['source']) }}">&rarr;</a>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="alma-dash-empty">
      <p>{{ __('No leads in this window yet. Campaign tags (utm_source) and referring sites will show up here automatically.', 'alma') }}</p>
    </div>
  @endif
</div>

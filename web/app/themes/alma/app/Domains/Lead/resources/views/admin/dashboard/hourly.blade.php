{{-- Dashboard → Hourly lead trend: leads per day at each hour of the site's clock, over a range picked in the widget. --}}
<div class="alma-dash-chart-card">
  <div class="alma-dash-chart-header alma-dash-hourly-header">
    <div>
      <span class="alma-dash-box-subtitle">{{ __('Leads per day by hour', 'alma') }}</span>
      <div class="alma-dash-box-meta">{{ $summary }}</div>
    </div>
    <nav class="alma-dash-range" aria-label="{{ __('Hourly lead trend range', 'alma') }}">
      @foreach ($ranges as $range)
        <a href="{{ $range['url'] }}" @class(['alma-dash-range-tab', 'alma-dash-range-active' => $range['active']]) @if ($range['active']) aria-current="true" @endif>{{ $range['label'] }}</a>
      @endforeach
    </nav>
  </div>
  <div class="alma-dash-chart-body">
    <div class="alma-dash-barchart-grid alma-dash-barchart-hourly">
      @foreach ($bars as $bar)
        <div class="alma-dash-bar-col">
          <div class="alma-dash-bar-val-wrap">
            @if ($bar['count'] > 0)
              <span @class(['alma-dash-bar-val', 'alma-val-peak' => $bar['isPeak']])>{{ number_format_i18n($bar['average'], 1) }}</span>
            @else
              <span class="alma-dash-bar-val-empty">&ndash;</span>
            @endif
          </div>
          <div class="alma-dash-bar-slot">
            @if ($bar['count'] > 0)
              <div @class(['alma-dash-bar-fill-v', 'alma-bar-peak' => $bar['isPeak']]) style="height: {{ $bar['pct'] }}%;" title="{{ $bar['title'] }}"></div>
            @else
              <div class="alma-dash-bar-fill-zero" title="{{ $bar['title'] }}"></div>
            @endif
          </div>
          <div class="alma-dash-bar-label-wrap">
            <span @class(['alma-dash-bar-day', 'alma-day-peak' => $bar['isPeak']])>{!! $bar['label'] !== null ? e($bar['label']) : '&nbsp;' !!}</span>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="alma-dash-card-footer">
  @if ($busiest !== null && $quietest !== null)
    <span class="alma-dash-footer-meta">{{ sprintf(__('Busiest %1$s. Quietest %2$s.', 'alma'), $busiest, $quietest) }}</span>
  @else
    <span class="alma-dash-footer-meta">{{ __('No leads captured in this range yet.', 'alma') }}</span>
  @endif
</div>

{{-- Dashboard → Lead performance: four tiles, the pipeline funnel and the daily volume chart, all over the same window. --}}
<div class="alma-dash-kpi-wrap">
  <div class="alma-dash-kpi-grid">
    <div class="alma-dash-kpi-card">
      <div class="alma-dash-kpi-header">
        <span class="alma-dash-kpi-label">{{ __('Submissions', 'alma') }}</span>
        <svg class="alma-dash-kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="alma-dash-kpi-number">{{ number_format_i18n($total) }}</div>
      <div class="alma-dash-kpi-meta">{{ sprintf(__('Leads captured in the last %d days', 'alma'), $windowDays) }}</div>
    </div>

    <div class="alma-dash-kpi-card">
      <div class="alma-dash-kpi-header">
        <span class="alma-dash-kpi-label">{{ __('Qualified', 'alma') }}</span>
        <span class="alma-dash-badge-dark">{{ __('Pipeline', 'alma') }}</span>
      </div>
      <div class="alma-dash-kpi-number">{{ number_format_i18n($qualified) }}</div>
      <div class="alma-dash-kpi-meta">{{ sprintf(__('%s%% qualified or won', 'alma'), $qualifiedRate) }}</div>
    </div>

    <div class="alma-dash-kpi-card">
      <div class="alma-dash-kpi-header">
        <span class="alma-dash-kpi-label">{{ __('Won', 'alma') }}</span>
        <svg class="alma-dash-kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
      </div>
      <div class="alma-dash-kpi-number">{{ number_format_i18n($won) }}</div>
      <div class="alma-dash-kpi-meta">{{ __('Of those leads, now customers', 'alma') }}</div>
    </div>

    <div class="alma-dash-kpi-card">
      <div class="alma-dash-kpi-header">
        <span class="alma-dash-kpi-label">{{ __('Conversion', 'alma') }}</span>
        <svg class="alma-dash-kpi-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      </div>
      <div class="alma-dash-kpi-number">{{ $wonRate }}%</div>
      <div class="alma-dash-kpi-meta">{{ __('Won / submissions, same cohort', 'alma') }}</div>
    </div>
  </div>

  <div class="alma-dash-funnel-box">
    <div class="alma-dash-funnel-header">
      <span class="alma-dash-box-subtitle">{{ __('Pipeline conversion funnel', 'alma') }}</span>
      <span class="alma-dash-box-meta">{{ sprintf(__('Last %1$d days: %2$s captured → %3$s won', 'alma'), $windowDays, number_format_i18n($total), number_format_i18n($won)) }}</span>
    </div>
    <div class="alma-dash-funnel-stages">
      <div class="alma-dash-funnel-stage">
        <div class="alma-dash-stage-info">
          <span class="alma-dash-stage-name">{{ __('1. Captured', 'alma') }}</span>
          <span class="alma-dash-stage-val">{{ number_format_i18n($total) }} (100%)</span>
        </div>
        <div class="alma-dash-bar-track"><div class="alma-dash-bar-fill alma-stage-1" style="width: 100%;"></div></div>
      </div>
      <div class="alma-dash-funnel-stage">
        <div class="alma-dash-stage-info">
          <span class="alma-dash-stage-name">{{ __('2. Contacted', 'alma') }}</span>
          <span class="alma-dash-stage-val">{{ number_format_i18n($contacted) }} ({{ $contactedRate }}%)</span>
        </div>
        <div class="alma-dash-bar-track"><div class="alma-dash-bar-fill alma-stage-2" style="width: {{ max(4, min(100, $contactedRate)) }}%;"></div></div>
      </div>
      <div class="alma-dash-funnel-stage">
        <div class="alma-dash-stage-info">
          <span class="alma-dash-stage-name">{{ __('3. Qualified', 'alma') }}</span>
          <span class="alma-dash-stage-val">{{ number_format_i18n($qualified) }} ({{ $qualifiedRate }}%)</span>
        </div>
        <div class="alma-dash-bar-track"><div class="alma-dash-bar-fill alma-stage-2" style="width: {{ max(4, min(100, $qualifiedRate)) }}%;"></div></div>
      </div>
      <div class="alma-dash-funnel-stage">
        <div class="alma-dash-stage-info">
          <span class="alma-dash-stage-name">{{ __('4. Won', 'alma') }}</span>
          <span class="alma-dash-stage-val">{{ number_format_i18n($won) }} ({{ $wonRate }}%)</span>
        </div>
        <div class="alma-dash-bar-track"><div class="alma-dash-bar-fill alma-stage-3" style="width: {{ max(4, min(100, $wonRate)) }}%;"></div></div>
      </div>
    </div>
  </div>

  <div class="alma-dash-chart-card">
    <div class="alma-dash-chart-header">
      <div>
        <span class="alma-dash-box-subtitle">{{ sprintf(__('%d-day ingestion volume', 'alma'), $windowDays) }}</span>
        <div class="alma-dash-box-meta">{{ __('Daily lead capture trend', 'alma') }}</div>
      </div>
      <span class="alma-dash-badge-subtle">{{ sprintf(__('Trailing %d days', 'alma'), $windowDays) }}</span>
    </div>
    <div class="alma-dash-chart-body">
      <div class="alma-dash-barchart-grid">
        @foreach ($bars as $bar)
          <div class="alma-dash-bar-col">
            <div class="alma-dash-bar-val-wrap">
              @if ($bar['count'] > 0)
                <span @class(['alma-dash-bar-val', 'alma-val-peak' => $bar['isPeak']])>{{ $bar['count'] }}</span>
              @else
                <span class="alma-dash-bar-val-empty">&ndash;</span>
              @endif
            </div>
            <div class="alma-dash-bar-slot">
              @if ($bar['count'] > 0)
                <div @class(['alma-dash-bar-fill-v', 'alma-bar-peak' => $bar['isPeak']]) style="height: {{ $bar['pct'] }}%;" title="{{ sprintf(_n('%1$s: %2$d lead', '%1$s: %2$d leads', $bar['count'], 'alma'), $bar['label'], $bar['count']) }}"></div>
              @else
                <div class="alma-dash-bar-fill-zero" title="{{ sprintf(__('%s: 0 leads', 'alma'), $bar['label']) }}"></div>
              @endif
            </div>
            <div class="alma-dash-bar-label-wrap">
              <span @class(['alma-dash-bar-day', 'alma-day-peak' => $bar['isPeak']])>{{ $bar['label'] }}</span>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="alma-dash-card-footer">
    <a href="{{ \App\Domains\Lead\Admin\LeadsAdmin::url() }}" class="alma-dash-link">{{ __('Open the full lead intelligence hub', 'alma') }} &rarr;</a>
  </div>
</div>

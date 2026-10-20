{{-- Dashboard → Platform & Infrastructure Health: the environment, every health check, and each third party the site called lately. --}}
<div class="alma-dash-health-wrap">
  <div class="alma-dash-health-item">
    <div class="alma-dash-health-left">
      <span class="alma-dash-indicator-green"></span>
      <div>
        <div class="alma-dash-health-name">{{ __('Environment', 'alma') }}</div>
        <div class="alma-dash-health-desc">{{ sprintf(__('Release %s', 'alma'), $platform['release']) }}</div>
      </div>
    </div>
    <span class="alma-badge-zinc">{{ $platform['env'] }}</span>
  </div>

  @foreach ($checks as $row)
    @include('admin.dashboard._health-row', ['row' => $row])
  @endforeach

  @foreach ($integrations as $row)
    @include('admin.dashboard._health-row', ['row' => $row])
  @endforeach

  <div class="alma-dash-health-item">
    <div class="alma-dash-health-left">
      <span class="alma-dash-indicator-green"></span>
      <div>
        <div class="alma-dash-health-name">{{ __('PHP runtime', 'alma') }}</div>
        <div class="alma-dash-health-desc">{{ sprintf(__('Memory limit: %s', 'alma'), $platform['memory_limit']) }}</div>
      </div>
    </div>
    <span class="alma-badge-zinc">PHP {{ $platform['php'] }}</span>
  </div>

  <div class="alma-dash-card-footer">
    <span class="alma-dash-footer-meta">{{ $integrations === [] ? __('No outbound calls in the last 7 days.', 'alma') : __('Integrations judged from the last 50 calls each.', 'alma') }}</span>
    <a href="{{ admin_url('site-health.php') }}" class="alma-dash-link">{{ __('Open Site Health', 'alma') }} &rarr;</a>
  </div>
</div>

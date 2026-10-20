{{-- Dashboard → Shortcuts & Quick Actions: core's, then each module's (alma/dashboard/shortcuts). --}}
<div class="alma-dash-shortcuts-grid">
  @foreach ($shortcuts as $shortcut)
    <a href="{{ $shortcut['url'] }}" class="alma-dash-shortcut-card" @if (! empty($shortcut['external'])) target="_blank" rel="noopener" @endif>
      <div class="alma-dash-shortcut-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $shortcut['icon'] !!}</svg>
      </div>
      <div>
        <div class="alma-dash-shortcut-title">{{ $shortcut['title'] }}</div>
        <div class="alma-dash-shortcut-desc">{{ $shortcut['description'] }}</div>
      </div>
    </a>
  @endforeach
</div>

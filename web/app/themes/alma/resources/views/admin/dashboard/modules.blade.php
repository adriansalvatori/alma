{{-- Dashboard → Modules & Services Status: every module in config/alma.php, what it is, whether it runs here. --}}
@php
  $states = [
    'active' => ['indicator' => 'alma-dash-indicator-green', 'badge' => 'alma-badge-dark', 'label' => __('Active', 'alma')],
    'unavailable' => ['indicator' => 'alma-dash-indicator-amber', 'badge' => 'alma-badge-amber', 'label' => __('Prerequisite missing', 'alma')],
    'disabled' => ['indicator' => 'alma-dash-indicator-red', 'badge' => 'alma-badge-zinc', 'label' => __('Disabled', 'alma')],
  ];
@endphp

<div class="alma-dash-domains-wrap">
  <div class="alma-dash-domains-list">
    @foreach ($modules as $key => $module)
      @php($state = $states[$module['status']] ?? $states['disabled'])
      <div class="alma-dash-domain-card">
        <div class="alma-dash-domain-top">
          <div class="alma-dash-domain-title-wrap">
            <span class="{{ $state['indicator'] }}"></span>
            <span class="alma-dash-domain-title">{{ $module['title'] }}</span>
          </div>
          <span class="{{ $state['badge'] }}">{{ $state['label'] }}</span>
        </div>
        @if ($module['description'] !== '')
          <p class="alma-dash-domain-desc">{{ $module['description'] }}</p>
        @endif
        @if (! empty($module['chips']))
          <div class="alma-dash-domain-badges">
            @foreach ($module['chips'] as $chip)
              <span class="alma-dash-chip">{{ $chip }}</span>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>
</div>

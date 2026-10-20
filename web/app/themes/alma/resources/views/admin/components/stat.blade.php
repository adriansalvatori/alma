{{-- A KPI tile: a label, one number, an optional hint under it. With `href` it is a link (to the list it counts); `active` marks the filter in use; the `icon` slot takes an <svg>'s inner markup. --}}
@props(['label', 'value', 'hint' => null, 'href' => null, 'active' => false])

@php($tag = $href ? 'a' : 'div')

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['alma-stat', 'alma-stat--active' => $active]) }}>
  <div class="alma-stat__head">
    <span class="alma-stat__label">{{ $label }}</span>
    @isset($icon)
      <svg class="alma-stat__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{{ $icon }}</svg>
    @endisset
  </div>
  <div class="alma-stat__value">{{ $value }}</div>
  @if ($hint)
    <div class="alma-stat__hint">{{ $hint }}</div>
  @endif
</{{ $tag }}>

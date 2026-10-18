{{-- A KPI tile: a label, one number, an optional hint under it. --}}
@props(['label', 'value', 'hint' => null])

<div {{ $attributes->class(['alma-stat']) }}>
  <div class="alma-stat__label">{{ $label }}</div>
  <div class="alma-stat__value">{{ $value }}</div>
  @if ($hint)
    <div class="alma-stat__hint">{{ $hint }}</div>
  @endif
</div>

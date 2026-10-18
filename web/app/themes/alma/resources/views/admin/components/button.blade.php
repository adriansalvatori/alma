{{-- A link (with href) or a button. Variants: secondary (default), primary, danger, ghost; size: sm. Other attributes (name, value, form, onclick…) pass through. --}}
@props(['href' => null, 'variant' => 'secondary', 'size' => null, 'type' => 'button'])

@php
  $classes = ['alma-btn', 'alma-btn--'.$variant => $variant !== 'secondary', 'alma-btn--'.$size => $size !== null];
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
  <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif

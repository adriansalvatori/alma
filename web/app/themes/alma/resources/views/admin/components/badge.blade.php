{{-- A pill. Tones: neutral, success, warning, danger, info, brand. `dot` draws a status instead: white and outlined, with a dot in the tone's colour. --}}
@props(['tone' => 'neutral', 'dot' => false])

@if ($dot)
  <span {{ $attributes->class(['alma-badge', 'alma-badge--dot']) }}><span class="alma-badge__dot alma-badge__dot--{{ $tone }}"></span>{{ $slot }}</span>
@else
  <span {{ $attributes->class(['alma-badge', 'alma-badge--'.$tone => $tone !== 'neutral']) }}>{{ $slot }}</span>
@endif

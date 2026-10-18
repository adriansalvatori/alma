{{-- A status pill. Tones: neutral, success, warning, danger, info, brand. --}}
@props(['tone' => 'neutral'])

<span {{ $attributes->class(['alma-badge', 'alma-badge--'.$tone => $tone !== 'neutral']) }}>{{ $slot }}</span>

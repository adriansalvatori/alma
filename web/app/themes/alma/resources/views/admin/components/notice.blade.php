{{-- An inline callout that stays where it's put (core's .notice gets moved under the title). Tones: info, success, warning, danger. --}}
@props(['tone' => 'info'])

<div {{ $attributes->class(['alma-notice', 'alma-notice--'.$tone => $tone !== 'info']) }}>{{ $slot }}</div>

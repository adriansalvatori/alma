{{-- A labelled form row: the control goes in the slot. `locked` names the .env variable that pins the value, shown under the control. --}}
@props(['label', 'for' => null, 'hint' => null, 'locked' => null])

<div {{ $attributes->class(['alma-field']) }}>
  @if ($for)
    <label class="alma-field__label" for="{{ $for }}">{{ $label }}</label>
  @else
    <span class="alma-field__label">{{ $label }}</span>
  @endif

  {{ $slot }}

  @if ($hint)
    <p class="alma-field__hint">{{ $hint }}</p>
  @endif
  @if ($locked)
    <p class="alma-field__lock">
      <span class="dashicons dashicons-lock" aria-hidden="true"></span>
      {!! sprintf(esc_html__('Set by %s in .env for this environment.', 'alma'), '<code>'.esc_html($locked).'</code>') !!}
    </p>
  @endif
</div>

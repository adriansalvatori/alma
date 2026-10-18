{{-- What a list shows when there is nothing in it yet. --}}
@props(['title', 'description' => null])

<div {{ $attributes->class(['alma-empty']) }}>
  <p class="alma-empty__title">{{ $title }}</p>
  @if ($description)
    <p class="alma-empty__description">{{ $description }}</p>
  @endif
  {{ $slot }}
</div>

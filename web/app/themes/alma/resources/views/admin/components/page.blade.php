{{-- An alma admin screen: the .alma-admin wrapper, the title row with its actions, and the line core moves notices under. Every custom screen starts with this. --}}
@props(['title', 'description' => null])

<div {{ $attributes->class(['wrap', 'alma-admin']) }}>
  <div class="alma-admin-header">
    <div>
      <h1 class="alma-admin-title">{{ $title }}</h1>
      @if ($description)
        <p class="alma-admin-description">{{ $description }}</p>
      @endif
    </div>
    @isset($actions)
      <div class="alma-actions">{{ $actions }}</div>
    @endisset
  </div>
  <hr class="wp-header-end">

  {{ $slot }}
</div>

{{-- An alma admin screen: the .alma-admin wrapper, the title row with its actions, and the line core moves notices under. Every custom screen starts with this. A record's screen can pass a `heading` slot to draw its own header instead of the title row. --}}
@props(['title' => null, 'description' => null])

<div {{ $attributes->class(['wrap', 'alma-admin']) }}>
  @isset($heading)
    {{ $heading }}
  @else
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
  @endisset
  <hr class="wp-header-end">

  {{ $slot }}
</div>

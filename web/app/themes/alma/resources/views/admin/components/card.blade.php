{{-- A section of a screen: optional title, description and header actions; `flush` lets a table run edge to edge; an optional footer for form buttons. --}}
@props(['title' => null, 'description' => null, 'flush' => false])

<section {{ $attributes->class(['alma-card']) }}>
  @if ($title || isset($actions))
    <header class="alma-card__header">
      <div>
        @if ($title)
          <h2 class="alma-card__title">{{ $title }}</h2>
        @endif
        @if ($description)
          <p class="alma-card__description">{{ $description }}</p>
        @endif
      </div>
      @isset($actions)
        <div class="alma-actions">{{ $actions }}</div>
      @endisset
    </header>
  @endif

  <div @class(['alma-card__body', 'alma-card__body--flush' => $flush])>{{ $slot }}</div>

  @isset($footer)
    <footer class="alma-card__footer">{{ $footer }}</footer>
  @endisset
</section>

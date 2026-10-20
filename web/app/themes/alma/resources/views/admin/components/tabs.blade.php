{{-- A section's screens as segmented tabs: `tabs` is key => [label, url]; `active` is the current key. --}}
@props(['tabs', 'active' => null])

<nav {{ $attributes->class(['alma-tabs']) }}>
  @foreach ($tabs as $key => [$label, $url])
    <a href="{{ $url }}" @class(['alma-tab', 'alma-tab--active' => $key === $active]) @if ($key === $active) aria-current="page" @endif>{{ $label }}</a>
  @endforeach
</nav>

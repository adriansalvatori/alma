{{-- Font preloads: one <link> per self-hosted font file listed in content.fonts.preload. Empty by default (system font stack). --}}
@foreach ((array) config('content.fonts.preload', []) as $font)
  @php
    try {
      $href = \Illuminate\Support\Facades\Vite::asset($font);
    } catch (\Throwable) {
      $href = null;
    }
  @endphp
  @if ($href)
    <link rel="preload" as="font" type="font/woff2" href="{{ $href }}" crossorigin>
  @endif
@endforeach

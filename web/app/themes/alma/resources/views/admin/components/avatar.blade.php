{{--
  A person: tinted initials, with their company's logo laid over them when there is one. A logo
  that fails to load (an unknown domain answers 404) deletes itself and the initials show through.
  `referrerpolicy` keeps the admin URL, filters and all, from riding along to the logo service.
  Pass `initials`, `tint` ([background, text]) and `logo` (a URL or null); see LeadAvatar.
--}}
@props(['initials', 'tint' => ['#f4f4f5', '#09090b'], 'logo' => null, 'title' => null, 'size' => 36])

<div {{ $attributes->class(['alma-avatar']) }} style="--alma-avatar-size: {{ (int) $size }}px;">
  <span class="alma-avatar__initials" style="background: {{ $tint[0] }}; color: {{ $tint[1] }};">{{ $initials }}</span>
  @if ($logo)
    <img class="alma-avatar__logo" src="{{ $logo }}" alt="" width="{{ (int) $size }}" height="{{ (int) $size }}" @if ($title) title="{{ $title }}" @endif loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove();">
  @endif
</div>

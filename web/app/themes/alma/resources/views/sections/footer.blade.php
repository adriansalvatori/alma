{{-- Site footer: brand and tagline, the footer menu, the footer widget area and the copyright line. --}}
@php
  $links = [];

  if (has_nav_menu('footer_navigation')) {
    $locations = get_nav_menu_locations();
    $links = array_values(array_filter(
      wp_get_nav_menu_items($locations['footer_navigation'] ?? 0) ?: [],
      fn ($item) => (int) $item->menu_item_parent === 0,
    ));
  }
@endphp

<footer class="border-t border-zinc-200 bg-white pb-8 pt-16 dark:border-zinc-800 dark:bg-zinc-900">
  <div class="container-page">
    <div class="mb-12 flex flex-col gap-10 md:flex-row md:justify-between">
      <div class="max-w-sm">
        <a href="{{ home_url('/') }}" @navigate class="mb-4 flex items-center gap-2 text-xl font-bold text-zinc-900 dark:text-white">
          <flux:icon.rocket-launch class="h-6 w-6 text-brand-500" />
          <span>{!! $siteName !!}</span>
        </a>
        @if ($tagline = get_bloginfo('description', 'display'))
          <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $tagline }}</p>
        @endif
      </div>

      @if ($links)
        <nav aria-label="{{ __('Footer', 'alma') }}">
          <ul class="grid grid-cols-2 gap-x-12 gap-y-3 sm:grid-cols-3">
            @foreach ($links as $item)
              <li>
                <a href="{{ $item->url }}" @navigate class="text-sm text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">{{ $item->title }}</a>
              </li>
            @endforeach
          </ul>
        </nav>
      @endif
    </div>

    <div class="flex flex-col items-center justify-between gap-4 border-t border-zinc-200 pt-8 text-sm text-zinc-500 md:flex-row dark:border-zinc-800 dark:text-zinc-400">
      <p>&copy; {{ date('Y') }} {!! $siteName !!}</p>
      <div>@php(dynamic_sidebar('sidebar-footer'))</div>
    </div>
  </div>
</footer>

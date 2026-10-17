{{-- Site header: brand, primary menu, commerce actions (when the Commerce module is on) and account links (when the Account module is on). --}}
@php
  $menu = [];

  if (has_nav_menu('primary_navigation')) {
    $locations = get_nav_menu_locations();
    $menu = array_values(array_filter(
      wp_get_nav_menu_items($locations['primary_navigation'] ?? 0) ?: [],
      fn ($item) => (int) $item->menu_item_parent === 0,
    ));
  }

  $commerce = app()->providerIsLoaded(\App\Domains\Commerce\CommerceServiceProvider::class);
  $account = \Illuminate\Support\Facades\Route::has('login');
  $canRegister = \Illuminate\Support\Facades\Route::has('register') && get_option('users_can_register');
  $user = is_user_logged_in() ? wp_get_current_user() : null;
  $navigate = \App\Support\Navigate::attributes();
@endphp

<header class="sticky top-0 z-50 w-full border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/80" x-data="{ open: false }">
  <div class="container-page flex h-16 items-center justify-between gap-6">
    <a href="{{ home_url('/') }}" @navigate class="flex items-center gap-2 text-xl font-bold text-zinc-900 dark:text-white">
      <flux:icon.rocket-launch class="h-6 w-6 text-brand-500" />
      <span>{!! $siteName !!}</span>
    </a>

    <nav class="hidden flex-1 justify-center gap-8 md:flex" aria-label="{{ __('Primary', 'alma') }}">
      @foreach ($menu as $item)
        <a href="{{ $item->url }}" @navigate class="text-sm font-medium text-zinc-600 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">{{ $item->title }}</a>
      @endforeach
    </nav>

    <div class="hidden items-center gap-3 md:flex">
      <flux:button variant="subtle" class="px-2!" x-on:click="$flux.dark = ! $flux.dark" aria-label="{{ __('Toggle dark mode', 'alma') }}">
        <flux:icon.moon class="h-5 w-5 dark:hidden" />
        <flux:icon.sun class="hidden h-5 w-5 dark:block" />
      </flux:button>

      @if ($commerce)
        <livewire:commerce.search />
        <livewire:commerce.favorites />
        <livewire:commerce.cart />
      @endif

      @if ($account)
        @if ($user)
          <flux:dropdown>
            <flux:button variant="ghost" icon-trailing="chevron-down">{{ $user->display_name }}</flux:button>
            <flux:menu>
              <flux:menu.item icon="squares-2x2" :href="route('dashboard')" :attributes="$navigate">{{ __('Dashboard', 'alma') }}</flux:menu.item>
              <flux:menu.item icon="cog-8-tooth" :href="route('settings')" :attributes="$navigate">{{ __('Settings', 'alma') }}</flux:menu.item>
              <flux:menu.separator />
              <flux:menu.item icon="arrow-right-start-on-rectangle" :href="wp_logout_url(home_url('/'))">{{ __('Log out', 'alma') }}</flux:menu.item>
            </flux:menu>
          </flux:dropdown>
        @else
          <flux:button variant="ghost" :href="route('login')" :attributes="$navigate">{{ __('Log in', 'alma') }}</flux:button>
          @if ($canRegister)
            <flux:button variant="primary" :href="route('register')" :attributes="$navigate">{{ __('Sign up', 'alma') }}</flux:button>
          @endif
        @endif
      @endif
    </div>

    <div class="flex items-center gap-2 md:hidden">
      <flux:button variant="subtle" class="px-2!" x-on:click="$flux.dark = ! $flux.dark" aria-label="{{ __('Toggle dark mode', 'alma') }}">
        <flux:icon.moon class="h-5 w-5 dark:hidden" />
        <flux:icon.sun class="hidden h-5 w-5 dark:block" />
      </flux:button>
      <flux:button variant="ghost" icon="bars-3" x-on:click="open = ! open" aria-label="{{ __('Toggle menu', 'alma') }}" />
    </div>
  </div>

  <div x-show="open" x-cloak class="border-t border-zinc-200 bg-white p-4 shadow-lg md:hidden dark:border-zinc-800 dark:bg-zinc-900">
    <nav class="flex flex-col gap-4" aria-label="{{ __('Mobile', 'alma') }}">
      @foreach ($menu as $item)
        <a href="{{ $item->url }}" @navigate class="text-base font-medium text-zinc-900 dark:text-white">{{ $item->title }}</a>
      @endforeach

      @if ($account)
        <hr class="border-zinc-100 dark:border-zinc-800">
        @if ($user)
          <a href="{{ route('dashboard') }}" @navigate class="text-base font-medium text-zinc-900 dark:text-white">{{ __('Dashboard', 'alma') }}</a>
          <a href="{{ route('settings') }}" @navigate class="text-base font-medium text-zinc-900 dark:text-white">{{ __('Settings', 'alma') }}</a>
          <a href="{{ wp_logout_url(home_url('/')) }}" class="text-base font-medium text-red-600 dark:text-red-400">{{ __('Log out', 'alma') }}</a>
        @else
          <div class="grid grid-cols-2 gap-4">
            <flux:button variant="outline" :href="route('login')" :attributes="$navigate">{{ __('Log in', 'alma') }}</flux:button>
            @if ($canRegister)
              <flux:button variant="primary" :href="route('register')" :attributes="$navigate">{{ __('Sign up', 'alma') }}</flux:button>
            @endif
          </div>
        @endif
      @endif
    </nav>
  </div>
</header>

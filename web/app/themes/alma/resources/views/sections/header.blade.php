<header class="bg-white shadow-md">
  <div class="container mx-auto px-6 py-3">
    <div class="flex items-center justify-between">
      <a class="text-3xl font-bold text-gray-800 hover:text-gray-700" href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>
      @if (has_nav_menu('primary_navigation'))
        <nav class="hidden md:flex space-x-6 text-lg">
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'flex space-x-6', 'echo' => false]) !!}
        </nav>
      @endif
    </div>
  </div>
</header>


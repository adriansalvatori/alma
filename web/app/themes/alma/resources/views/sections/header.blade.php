<header class="sticky top-0 z-50 w-full border-b border-zinc-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Brand -->
            <div class="shrink-0 flex items-center">
                <a href="{{ home_url('/') }}"
                    class="text-xl font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                    <flux:icon.rocket-launch class="w-6 h-6 text-indigo-500" />
                    <span>{!! $siteName !!}</span>
                </a>
            </div>

            <!-- Primary Navigation (Desktop) -->
            <nav class="hidden md:flex flex-1 justify-center space-x-8">
                @if (has_nav_menu('primary_navigation'))
                    @php
                        $locations = get_nav_menu_locations();
                        $menu_id = $locations['primary_navigation'] ?? null;
                        $menu_items = $menu_id ? wp_get_nav_menu_items($menu_id) : [];
                    @endphp
                    @if ($menu_items)
                        @foreach ($menu_items as $item)
                            @if ($item->menu_item_parent == 0)
                                <a href="{{ $item->url }}"
                                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors">
                                    {{ $item->title }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endif
            </nav>

            <!-- Actions (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <!-- Dark Mode Toggle -->
                <flux:button variant="subtle" aria-label="Toggle dark mode" x-data
                    x-on:click="$flux.dark = ! $flux.dark" class="px-2!">
                    <flux:icon.moon class="w-5 h-5 dark:hidden" />
                    <flux:icon.sun class="w-5 h-5 hidden dark:block" />
                </flux:button>

                <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>

                @if (class_exists('WooCommerce'))
                    <livewire:commerce-search />
                    <livewire:commerce-favorites />
                    <livewire:commerce-cart />

                    <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700 mx-1"></div>
                @endif

                @if (is_user_logged_in())
                    @php $current_user = wp_get_current_user(); @endphp
                    <flux:dropdown>
                        <flux:button variant="ghost" icon-trailing="chevron-down">
                            {{ $current_user->display_name }}
                        </flux:button>
                        <flux:menu>
                            <flux:menu.item icon="user" href="/profile">Profile</flux:menu.item>
                            <flux:menu.item icon="cog-8-tooth" href="/settings">Settings</flux:menu.item>
                            <flux:menu.item icon="heart" href="/wishlist">Wishlist</flux:menu.item>
                            <flux:menu.separator />
                            <flux:menu.item icon="arrow-right-start-on-rectangle" href="/logout">Log out
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <flux:button variant="ghost" href="/login">Log in</flux:button>
                    <flux:button variant="primary" href="/register">Sign up</flux:button>
                @endif
            </div>

            <!-- Mobile Menu Toggle -->
            <div class="flex md:hidden items-center gap-2">
                <!-- Mobile Dark Mode Toggle -->
                <flux:button variant="subtle" aria-label="Toggle dark mode" x-data
                    x-on:click="$flux.dark = ! $flux.dark" class="px-2!">
                    <flux:icon.moon class="w-5 h-5 dark:hidden" />
                    <flux:icon.sun class="w-5 h-5 hidden dark:block" />
                </flux:button>

                <div x-data="{ mobileMenuOpen: false }">
                    <flux:button variant="ghost" icon="bars-3" @click="mobileMenuOpen = !mobileMenuOpen"
                        aria-label="Toggle menu" />

                    <!-- Simplified Mobile Menu (Alpine) -->
                    <div x-show="mobileMenuOpen" x-cloak
                        class="absolute top-16 left-0 right-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 p-4 shadow-lg text-left">
                        <div class="flex flex-col space-y-4">
                            @if (has_nav_menu('primary_navigation'))
                                @if (isset($menu_items) && $menu_items)
                                    @foreach ($menu_items as $item)
                                        @if ($item->menu_item_parent == 0)
                                            <a href="{{ $item->url }}"
                                                class="text-base font-medium text-zinc-900 dark:text-white">
                                                {{ $item->title }}
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                            @endif
                            <hr class="border-zinc-100 dark:border-zinc-800" />
                            @if (is_user_logged_in())
                                @php $current_user = wp_get_current_user(); @endphp
                                <div class="px-2 pb-2">
                                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-2">Signed in as
                                        <span
                                            class="text-zinc-900 dark:text-white">{{ $current_user->display_name }}</span>
                                    </div>
                                    <div class="flex flex-col space-y-2">
                                        <a href="/profile"
                                            class="text-base font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                                            <flux:icon.user class="w-5 h-5 text-zinc-400" />Profile
                                        </a>
                                        <a href="/settings"
                                            class="text-base font-medium text-zinc-900 dark:text-white flex items-center gap-2"><flux:icon.cog-8-tooth
                                                class="w-5 h-5 text-zinc-400" />Settings</a>
                                        <a href="/wishlist"
                                            class="text-base font-medium text-zinc-900 dark:text-white flex items-center gap-2">
                                            <flux:icon.heart class="w-5 h-5 text-zinc-400" />Wishlist
                                        </a>
                                        <a href="/logout"
                                            class="text-base font-medium text-red-600 dark:text-red-400 flex items-center gap-2 mt-2"><flux:icon.arrow-right-start-on-rectangle
                                                class="w-5 h-5 text-red-400" />Log out</a>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-4">
                                    <flux:button variant="outline" href="/login">Log in</flux:button>
                                    <flux:button variant="primary" href="/register">Sign up</flux:button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

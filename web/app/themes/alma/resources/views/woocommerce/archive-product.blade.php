{{--
The Template for displaying product archives, including the main shop page which is a post type archive

This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see https://docs.woocommerce.com/document/template-structure/
@package WooCommerce/Templates
@version 3.4.0
--}}

@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @php
            do_action('get_header', 'shop');
            do_action('woocommerce_before_main_content');

            $current_cat = is_product_category() ? get_queried_object() : null;

            // Get Top level categories
            $top_cats = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => 0,
                'hide_empty' => false,
            ]);
        @endphp

        <div class="flex flex-col lg:flex-row gap-10">
            {{-- Custom Shop Sidebar --}}
            <aside class="w-full lg:w-64 shrink-0">
                <div class="sticky top-24 pr-4">
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white mb-6">Category</h2>

                    {{-- Category Accordions --}}
                    <div class="mb-8 flex flex-col gap-1">
                        @if (is_array($top_cats) || is_object($top_cats))
                            @foreach ($top_cats as $cat)
                                @if (!is_wp_error($cat))
                                    @php
                                        $sub_cats = get_terms([
                                            'taxonomy' => 'product_cat',
                                            'parent' => $cat->term_id,
                                            'hide_empty' => false,
                                        ]);
                                        $has_subs = !empty($sub_cats) && !is_wp_error($sub_cats);

                                        // Determine if accordion should be open by default
                                        // If viewing this category or one of its subcategories
                                        $isOpen = false;
                                        if (
                                            $current_cat &&
                                            ($current_cat->term_id === $cat->term_id ||
                                                $current_cat->parent === $cat->term_id)
                                        ) {
                                            $isOpen = true;
                                        }

                                        $icon = 'folder';
                                        $name_lower = strtolower($cat->name);
                                        if (str_contains($name_lower, 'home')) {
                                            $icon = 'home';
                                        } elseif (str_contains($name_lower, 'music')) {
                                            $icon = 'musical-note';
                                        } elseif (str_contains($name_lower, 'phone')) {
                                            $icon = 'device-phone-mobile';
                                        } elseif (str_contains($name_lower, 'storage')) {
                                            $icon = 'archive-box';
                                        } elseif (str_contains($name_lower, 'tech')) {
                                            $icon = 'computer-desktop';
                                        }
                                    @endphp

                                    <div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }" class="w-full">
                                        {{-- Accordion Header --}}
                                        <button @click="open = !open"
                                            class="flex items-center justify-between w-full p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group">
                                            <div class="flex items-center gap-3">
                                                @if ($icon === 'home')
                                                    <flux:icon.home
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @elseif($icon === 'musical-note')
                                                    <flux:icon.musical-note
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @elseif($icon === 'device-phone-mobile')
                                                    <flux:icon.device-phone-mobile
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @elseif($icon === 'archive-box')
                                                    <flux:icon.archive-box
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @elseif($icon === 'computer-desktop')
                                                    <flux:icon.computer-desktop
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @else
                                                    <flux:icon.folder
                                                        class="w-5 h-5 text-zinc-400 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                                @endif
                                                <span
                                                    class="text-sm font-medium text-zinc-700 dark:text-zinc-300 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                                    {!! wp_specialchars_decode($cat->name) !!}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                @if ($cat->count > 0)
                                                    <span
                                                        class="text-xs font-semibold text-zinc-400">{{ $cat->count }}</span>
                                                @endif
                                                @if ($has_subs)
                                                    <flux:icon.chevron-down
                                                        class="w-4 h-4 text-zinc-400 transition-transform duration-200"
                                                        x-bind:class="open ? 'rotate-180' : ''" />
                                                @endif
                                            </div>
                                        </button>

                                        {{-- Subcategories List (Tree) --}}
                                        @if ($has_subs)
                                            <div x-show="open" x-collapse x-cloak>
                                                <div
                                                    class="pl-4 border-l border-zinc-200 dark:border-zinc-800 ml-[19px] py-2 flex flex-col gap-1">
                                                    @foreach ($sub_cats as $sub)
                                                        @php
                                                            $isSubActive =
                                                                $current_cat && $current_cat->term_id === $sub->term_id;
                                                        @endphp
                                                        <a href="{{ get_term_link($sub) }}"
                                                            class="flex items-center justify-between py-1.5 pl-3 pr-2 text-sm font-medium {{ $isSubActive ? 'text-zinc-900 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }} hover:text-zinc-900 dark:hover:text-white transition-colors relative group">
                                                            {{-- The horizontal tree branch line --}}
                                                            <div
                                                                class="absolute -left-px top-1/2 -translate-y-1/2 w-3 h-px {{ $isSubActive ? 'bg-zinc-900 dark:bg-zinc-100' : 'bg-zinc-200 dark:bg-zinc-800' }} group-hover:bg-zinc-400 transition-colors">
                                                            </div>

                                                            <span>{!! wp_specialchars_decode($sub->name) !!}</span>
                                                            @if ($sub->count > 0)
                                                                <span
                                                                    class="text-xs text-zinc-400">{{ $sub->count }}</span>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    {{-- Filter Links --}}
                    <div class="flex flex-col gap-1 mt-8 pt-6 border-t border-zinc-200 dark:border-zinc-800">
                        @php
                            $current_orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order';
                            $is_on_sale = isset($_GET['onsale']) && $_GET['onsale'] === '1';
                        @endphp

                        <a href="{{ add_query_arg('orderby', 'date', remove_query_arg('onsale', wc_get_page_permalink('shop'))) }}"
                            class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group {{ $current_orderby === 'date' && !$is_on_sale ? 'bg-zinc-100 dark:bg-zinc-800/50' : '' }}">
                            <span
                                class="flex items-center gap-3 text-sm font-medium {{ $current_orderby === 'date' && !$is_on_sale ? 'text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }} group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                <flux:icon.magnifying-glass
                                    class="w-5 h-5 {{ $current_orderby === 'date' && !$is_on_sale ? 'text-zinc-600 dark:text-zinc-300' : 'text-zinc-400' }} group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                New Arrival
                            </span>
                        </a>

                        <a href="{{ add_query_arg('orderby', 'popularity', remove_query_arg('onsale', wc_get_page_permalink('shop'))) }}"
                            class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group {{ $current_orderby === 'popularity' && !$is_on_sale ? 'bg-zinc-100 dark:bg-zinc-800/50' : '' }}">
                            <span
                                class="flex items-center gap-3 text-sm font-medium {{ $current_orderby === 'popularity' && !$is_on_sale ? 'text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }} group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                <flux:icon.sparkles
                                    class="w-5 h-5 {{ $current_orderby === 'popularity' && !$is_on_sale ? 'text-zinc-600 dark:text-zinc-300' : 'text-zinc-400' }} group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                Best Seller
                            </span>
                        </a>

                        <a href="{{ add_query_arg('onsale', '1', remove_query_arg('orderby', wc_get_page_permalink('shop'))) }}"
                            class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800/50 transition-colors group {{ $is_on_sale ? 'bg-zinc-100 dark:bg-zinc-800/50' : '' }}">
                            <span
                                class="flex items-center gap-3 text-sm font-medium {{ $is_on_sale ? 'text-zinc-900 dark:text-white' : 'text-zinc-700 dark:text-zinc-300' }} group-hover:text-zinc-900 dark:group-hover:text-white transition-colors">
                                <flux:icon.receipt-percent
                                    class="w-5 h-5 {{ $is_on_sale ? 'text-zinc-600 dark:text-zinc-300' : 'text-zinc-400' }} group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors" />
                                On Discount
                            </span>
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Main Shop Grid --}}
            <main class="flex-1">

                <header class="woocommerce-products-header">
                    @if (apply_filters('woocommerce_show_page_title', true))
                        <h1 class="woocommerce-products-header__title page-title">{!! woocommerce_page_title(false) !!}</h1>
                    @endif

                    @php
                        do_action('woocommerce_archive_description');
                    @endphp
                </header>

                @if (woocommerce_product_loop())
                    @php
                        do_action('woocommerce_before_shop_loop');
                        woocommerce_product_loop_start();
                    @endphp

                    @if (wc_get_loop_prop('total'))
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @while (have_posts())
                                @php
                                    the_post();
                                    do_action('woocommerce_shop_loop');
                                    wc_get_template_part('content', 'product');
                                @endphp
                            @endwhile
                        </div>
                    @endif

                    @php
                        woocommerce_product_loop_end();
                        do_action('woocommerce_after_shop_loop');
                    @endphp
                @else
                    @php
                        do_action('woocommerce_no_products_found');
                    @endphp
                @endif

                @php
                    do_action('woocommerce_after_main_content');
                @endphp
        </div> {{-- End 2-column Flex Wrapper --}}

        @php
            // do_action('get_sidebar', 'shop'); // We are building a custom sidebar inside the layout
            do_action('get_footer', 'shop');
        @endphp
    </div>
@endsection

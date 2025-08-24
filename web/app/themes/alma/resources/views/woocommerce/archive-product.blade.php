{{--
The Template for displaying product archives, including the main shop page which is a post type archive

This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@seehttps://docs.woocommerce.com/document/template-structure/
@packageWooCommerce/Templates
@version3.4.0
--}}

@extends('layouts.app')

@section('content')
    <div class="container py-6">
        @action('get_header', 'shop')
        @action('woocommerce_before_main_content')

        @if (woocommerce_product_loop())
            @if (apply_filters('woocommerce_show_page_title', true))
                <div class="columns">
                    <div class="column is-half">
                        <h1 data-inertia data-inertia-reveal class="title is-5">
                            <span>{!! woocommerce_page_title(false) !!}</span>
                        </h1>
                    </div>
                    <div class="column is-half is-flex is-justify-content-flex-end">
                        @action('woocommerce_before_shop_loop')
                        {{ woocommerce_product_loop_start() }}
                    </div>
                </div>
            @endif
            @if (wc_get_loop_prop('total'))
                <div class="container" data-cursor-text="scroll">
                    <div class="columns is-multiline">
                        @while (have_posts())
                            {{ the_post() }}
                            @action('woocommerce_shop_loop')
                            @global($product)
                            @if ($product)
                                <div class="column is-4">
                                    <x-product.card :product="$product" />
                                </div>
                            @endif
                        @endwhile
                    </div>
                </div>
            @endif
            {{ woocommerce_product_loop_end() }}
            @action('woocommerce_after_shop_loop')
        @else
            @action('woocommerce_no_products_found')
        @endif
        @action('woocommerce_after_main_content')
        @action('get_sidebar', 'shop')
        @action('get_footer', 'shop')
    </div>
    <div class="container">
        <x-product.featured-carousel />
    </div>
@endsection

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
@php
  do_action('get_header', 'shop');
  do_action('woocommerce_before_main_content');
@endphp

@if(woocommerce_product_loop())
  @if(apply_filters('woocommerce_show_page_title', true))
    <div class="container has-margin-top-140 has-margin-bottom-50">
      <div class="columns">
        <div class="column is-half">
          <h1 data-inertia data-inertia-reveal class="title is-5">
            <span>{!! woocommerce_page_title(false) !!}</span>
            <span>@svg('decorator')</span>
          </h1>
        </div>
        <div class="column is-half is-flex justify-end">
          @php
            do_action('woocommerce_before_shop_loop');
            woocommerce_product_loop_start();
          @endphp
        </div>
      </div>
    </div>
  @endif
  

  @if(wc_get_loop_prop('total'))
    <div class="container" data-cursor-text="scroll">
      <div class="columns is-multiline">
        @while(have_posts())
          @php
            the_post();
            do_action('woocommerce_shop_loop');
          @endphp
          @global($product)
          <div class="column is-4">
            <x-product-card :product="$product" />
          </div>
          @endwhile
      </div>
    </div>
  @endif
  @php
    woocommerce_product_loop_end();
    do_action('woocommerce_after_shop_loop');
  @endphp
@else
  @php
    do_action('woocommerce_no_products_found')
  @endphp
@endif

@php
  do_action('woocommerce_after_main_content');
  do_action('get_sidebar', 'shop');
  do_action('get_footer', 'shop');
@endphp

@include('partials.billboard-list', [
    'billboards' => get_field('billboards_section_1', 81)
    ])

@include('partials.product-slideshow', [
    'title' => __('<b>Best Sellers</b>'),
    'tags' => [],
    'category' => '',
    ])
@endsection

@extends('layouts.app')

@section('content')
    @action('get_header', 'shop')
    <div class="container py-6">
        @while (have_posts())
            {{ the_post() }}
            @global($product)
            @set($gallery, App\single_product_gallery())
            <div class="columns is-relative">
                <div class="column is-5">
                    <div class="columns is-multiline gallery">
                        @foreach ($gallery as $image)
                            <div class="column is-12 gallery-image-container">
                                <div class="box has-background-light is-shadowless p-6">
                                    <img src="{{ $image }}" alt="">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="column is-6 is-offset-1">
                    <div class="content" style="position: sticky; top: 120px; bottom: 40px;">
                        @action('woocommerce_before_main_content')
                        <h1 class="is-size-1 has-text-weight-bold">
                            @title
                        </h1>
                        <p>
                            @excerpt
                        </p>
                        <div class="box has-background-light is-shadowless">
                            <x-product.store-actions />
                        </div>
                        @content
                        @action('woocommerce_after_main_content')
                    </div>
                </div>
            </div>
        @endwhile
    </div>
    <div class="section">
        <x-product.featured-carousel />
    </div>

    @action('get_sidebar', 'shop')
    @action('get_footer', 'shop')
@endsection

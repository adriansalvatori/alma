@query([
    'post_type'   => 'product',
    'product_tag' => $tags,
    'product_cat' => $category
])
<div class="container has-margin-top-140 has-margin-bottom-50">
    <h2 data-inertia data-inertia-reveal class="title is-5">{!!$title!!}</h2>
</div>
<div class="carousel" 
    data-inertia data-inertia-speed="2" 
    data-inertia-direction="horizontal" 
    style="--carousel-item-width: 28vw; --carousel-item-mobile-width: 85vw"
>
    <div class="carousel-wrapper columns is-mobile">
        @posts
            <div class="column is-3 carousel-item" data-width="28vw" data-cursor-text="Drag">
                @set($product , get_the_ID())
                <x-product-card :product="$product"/>
            </div>
        @endposts
        @posts
            <div class="column is-3 carousel-item" data-width="28vw" data-cursor-text="Drag">
                @set($product , get_the_ID())
                <x-product-card :product="$product"/>
            </div>
        @endposts
        @posts
            <div class="column is-3 carousel-item" data-width="28vw" data-cursor-text="Drag">
                @set($product , get_the_ID())
                <x-product-card :product="$product"/>
            </div>
        @endposts
    </div>
</div>
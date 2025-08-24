 <x-carousel.container :title="'Productos Destacados'" :item_width="'20vw'" :item_width_mobile="'85vw'">
     @query([
         'post_type' => 'product',
         'tax_query' => [
             [
                 'taxonomy' => 'product_visibility',
                 'field' => 'name',
                 'terms' => 'featured',
             ],
         ],
     ])
     @posts
         @global($product)
         <x-carousel.item :width="'20vw'">
             <x-product.card :product="$product" />
         </x-carousel.item>
     @endposts
 </x-carousel.container>

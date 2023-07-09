@global($product)

<store-actions  data-cursor="-hidden" data-cursor-text=" " class="store-actions columns is-gapless level is-full-width"
x-data="{
    id: {{$product->get_id()}},
    added: false,
    text: '{{__('Add to Cart', 'sage')}}'
}" >
    <div class="column is-half">
        <a x-on:click="() => {$store.cart.add(id, $event.target); added = true; text = '{{__('Product Added', 'sage')}}'}" class="add-to-cart button is-nude has-text-dark">
            <span :class="{'is-hidden' : !added}" class="icon" >@feather('check')</span>
            <span :class="{'is-hidden' : added}" class="icon">@feather('shopping-cart')</span>
            <span class="has-margin-left-10 is-size-6" x-text="text"></span>
        </a> 
    </div>
   <div class="column is-half has-text-right">
    <a x-on:click="" class="button is-nude" data-tooltip="comming soon">
        @feather('gift')
    </a>
    <a x-on:click="" class="button is-nude" data-tooltip="comming soon">
        @feather('heart')
    </a>
    <a x-on:click="() => {$store.share.share('@title', '@permalink')}" class="button is-nude">
        @feather('share')
    </a>
   </div>
</store-actions>

@global($product)

<store-actions data-cursor="-hidden" data-cursor-text=" " class="store-actions level is-fullwidth" x-data="{
    id: {{ $product->get_id() }},
    added: false,
    text: '{{ __('Agregar', 'alma') }}'
}">
    <div class="level-left">
        <a x-on:click="() => {$store.cart.add(id, $event.target); added = true; text = '{{ __('Agregado', 'alma') }}'}"
            class="add-to-cart button is-dark is-outlined has-text-dark">
            <span :class="{ 'is-hidden': !added }" class="icon"><i class="fas fa-check"></i></span>
            <span :class="{ 'is-hidden': added }" class="icon"><i class="fas fa-shopping-cart"></i></span>
            <span class="has-margin-left-10 is-size-6" x-text="text"></span>
        </a>
    </div>
    <div class="level-right">
        <a x-on:click="" class="has-text-dark is-size-5" data-tooltip="comming soon">
            <i class="fas fa-gift"></i>
        </a>
        <a x-on:click="" class="has-text-dark is-size-5" data-tooltip="comming soon">
            <i class="fas fa-heart"></i>
        </a>
        <a x-on:click="() => {$store.share.share('@title', '@permalink')}" class="has-text-dark is-size-5">
            <i class="fas fa-share"></i>
        </a>
    </div>
</store-actions>

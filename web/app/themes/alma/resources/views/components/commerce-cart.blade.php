<?php

use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component {
    #[On('cart-updated')]
    public function renderCart()
    {
        // Force re-render when event is received
    }

    public function removeItem($cartItemKey)
    {
        if (WC()->cart) {
            WC()->cart->remove_cart_item($cartItemKey);
            $this->dispatch('cart-updated');
        }
    }

    public function getCartDataProperty()
    {
        $data = [
            'items' => [],
            'count' => 0,
            'subtotal' => '',
        ];

        if (class_exists('WooCommerce') && WC()->cart) {
            $data['count'] = WC()->cart->get_cart_contents_count();
            $data['subtotal'] = WC()->cart->get_cart_subtotal();

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                    $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                    $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                    $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);

                    $data['items'][$cart_item_key] = [
                        'name' => $product_name,
                        'thumbnail' => $thumbnail,
                        'price' => $product_price,
                        'permalink' => $product_permalink,
                        'quantity' => $cart_item['quantity'],
                    ];
                }
            }
        }

        return $data;
    }
};
?>

<div class="contents" x-data x-init="if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('added_to_cart', () => {
        $wire.dispatch('cart-updated');
    });
}">
    <flux:modal.trigger name="cart-flyout">
        <flux:button variant="subtle" aria-label="Cart" class="px-2! relative">
            <flux:icon.shopping-bag
                class="w-5 h-5 text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition-colors" />
            @if ($this->cartData['count'] > 0)
                <div
                    class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-zinc-900 dark:bg-white text-[10px] font-bold text-white dark:text-zinc-900 shadow-sm ring-2 ring-white dark:ring-zinc-900">
                    {{ $this->cartData['count'] }}
                </div>
            @endif
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="cart-flyout" flyout variant="floating" class="w-full sm:w-96!">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Cart</h2>
            </div>

            <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
                @if (empty($this->cartData['items']))
                    <div class="flex flex-col items-center justify-center h-full text-zinc-500 dark:text-zinc-400">
                        <flux:icon.shopping-bag class="w-12 h-12 mb-4 text-zinc-300 dark:text-zinc-600" />
                        <p>Your cart is empty.</p>
                    </div>
                @else
                    @foreach ($this->cartData['items'] as $cart_item_key => $item)
                        <div class="flex items-center gap-4 py-2" wire:key="cart-item-{{ $cart_item_key }}">
                            <div class="w-16 h-16 rounded-md overflow-hidden bg-zinc-100 flex-shrink-0">
                                {!! $item['thumbnail'] !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $item['permalink'] }}"
                                    class="text-sm font-medium text-zinc-900 dark:text-white hover:text-primary truncate block">
                                    {{ $item['name'] }}
                                </a>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                                    {{ $item['quantity'] }} &times; {!! $item['price'] !!}
                                </div>
                            </div>
                            <flux:button variant="subtle" icon="trash"
                                class="px-2! text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-950"
                                wire:click="removeItem('{{ $cart_item_key }}')" />
                        </div>
                    @endforeach
                @endif
            </div>

            @if (!empty($this->cartData['items']))
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-zinc-500 dark:text-zinc-400 text-sm">Subtotal</span>
                        <span class="text-lg font-bold text-zinc-900 dark:text-white">{!! $this->cartData['subtotal'] !!}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <flux:button href="{{ wc_get_cart_url() }}">View Cart</flux:button>
                        <flux:button variant="primary" href="{{ wc_get_checkout_url() }}">Checkout</flux:button>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>
</div>

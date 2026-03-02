@php
    /**
     * Simple product add to cart
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 7.0.1
     */

    defined('ABSPATH') || exit();

    global $product;

    if (!$product->is_purchasable()) {
        return;
    }

    echo wc_get_stock_html($product); // WPCS: XSS ok.
@endphp

@if ($product->is_in_stock())
    @php do_action( 'woocommerce_before_add_to_cart_form' ); @endphp

    <form class="cart mt-8 pt-8 border-t border-zinc-200 dark:border-zinc-800" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post"
        enctype='multipart/form-data'>
        @php do_action( 'woocommerce_before_add_to_cart_button' ); @endphp

        <div class="flex items-end gap-4">
            {{-- Quantity Input Wrapper --}}
            <div class="w-24">
                @php
                    do_action('woocommerce_before_add_to_cart_quantity');

                    // Native WooCommerce quantity field, we style the input via global css or wrapper
                    woocommerce_quantity_input([
                        'min_value' => apply_filters(
                            'woocommerce_quantity_input_min',
                            $product->get_min_purchase_quantity(),
                            $product,
                        ),
                        'max_value' => apply_filters(
                            'woocommerce_quantity_input_max',
                            $product->get_max_purchase_quantity(),
                            $product,
                        ),
                        'input_value' => isset($_POST['quantity'])
                            ? wc_stock_amount(wp_unslash($_POST['quantity']))
                            : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                        'classes' =>
                            'block w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-center sm:text-lg h-[52px]',
                    ]);

                    do_action('woocommerce_after_add_to_cart_quantity');
                @endphp
            </div>

            {{-- Add to Cart Button via Flux --}}
            <div class="grow">
                {{-- Native WooCommerce needs this exact name and value to add to cart --}}
                <input type="hidden" name="add-to-cart" value="{{ esc_attr($product->get_id()) }}" />

                {{-- Visible Flux Button --}}
                <flux:button type="submit" variant="primary"
                    class="single_add_to_cart_button button alt w-full text-lg h-[52px] shadow-lg hover:shadow-xl transition-shadow">
                    <flux:icon.shopping-cart class="w-5 h-5 mr-2" />
                    {{ esc_html($product->single_add_to_cart_text()) }}
                </flux:button>
            </div>
        </div>

        {{-- Payment Options Trust Signal --}}
        <div
            class="mt-6 flex flex-col items-center gap-3 bg-zinc-50 dark:bg-zinc-900/50 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800">
            <span class="text-xs font-medium tracking-wide text-zinc-500 uppercase">Guaranteed Safe Checkout</span>
            <div class="flex items-center gap-3 text-zinc-400 dark:text-zinc-600">
                <flux:icon.credit-card class="w-8 h-8" />
                <flux:icon.banknotes class="w-8 h-8" />
                <flux:icon.currency-dollar class="w-8 h-8" />
                <flux:icon.shield-check class="w-8 h-8" />
            </div>
        </div>

        @php do_action( 'woocommerce_after_add_to_cart_button' ); @endphp
    </form>

    @php do_action( 'woocommerce_after_add_to_cart_form' ); @endphp
@endif

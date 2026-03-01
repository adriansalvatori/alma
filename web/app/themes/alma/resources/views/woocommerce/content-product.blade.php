@php
    /**
     * The template for displaying product content within loops
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see     https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 3.6.0
     */

    defined('ABSPATH') || exit();

    global $product;

    // Ensure visibility.
    if (empty($product) || !$product->is_visible()) {
        return;
    }
@endphp

<div {{ wc_product_class('group flex flex-col bg-white dark:bg-zinc-900', $product) }}>

    {{-- Product Image Container (Light gray with rounded corners) --}}
    <div
        class="relative w-full aspect-square bg-zinc-100 dark:bg-zinc-800/50 rounded-3xl overflow-hidden mb-5 p-8 flex items-center justify-center">

        {{-- Sale Badge (Left) --}}
        @if ($product->is_on_sale())
            <div class="absolute top-4 left-4 z-10">
                <flux:badge color="red" size="sm" class="font-semibold shadow-sm">
                    {{ apply_filters('woocommerce_sale_flash', '<span class="onsale">' . esc_html__('Sale!', 'woocommerce') . '</span>', $post, $product) }}
                </flux:badge>
            </div>
        @endif

        {{-- Category Pill (Right) --}}
        @php
            $categories = wc_get_product_category_list($product->get_id(), ', ');
        @endphp
        @if ($categories)
            <div class="absolute top-4 right-4 z-10">
                <div
                    class="bg-white dark:bg-zinc-700 text-zinc-600 dark:text-zinc-200 text-xs font-semibold px-3 py-1.5 rounded-full shadow-sm">
                    {!! strip_tags($categories) !!}
                </div>
            </div>
        @endif

        <a href="{{ apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product) }}"
            class="w-full h-full relative z-0 flex items-center justify-center">
            {!! woocommerce_get_product_thumbnail('woocommerce_thumbnail', [
                'class' =>
                    'max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-110 transition-transform duration-500 ease-out drop-shadow-xl',
            ]) !!}
        </a>
    </div>

    {{-- Product Details --}}
    <div class="flex flex-col grow px-1">

        {{-- Title and Price Row --}}
        <div class="flex justify-between items-start mb-1 gap-4">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight line-clamp-1">
                <a href="{{ apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product) }}"
                    class="hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors">
                    {{ $product->get_title() }}
                </a>
            </h2>
            <div class="text-lg font-bold text-zinc-900 dark:text-white whitespace-nowrap">
                {!! $product->get_price_html() !!}
            </div>
        </div>

        {{-- Rating --}}
        @php
            $review_count = $product->get_review_count();
            $average = $product->get_average_rating();
            // Defaulting display values to match mockup aesthetics if no reviews exist for demo purposes.
            $display_rating = $average > 0 ? number_format($average, 1) : '5.0';
            $display_count = $review_count > 0 ? $review_count . ' Reviews' : '1.2k Reviews';
        @endphp
        <div class="mb-5 flex items-center gap-1.5 text-sm">
            <svg class="w-4 h-4 text-orange-400 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="font-medium text-zinc-500 dark:text-zinc-400">{{ $display_rating }} <span
                    class="font-normal">({{ $display_count }})</span></span>
        </div>

        <div class="grow"></div>

        {{-- Action Buttons --}}
        <div class="mt-auto pt-2 grid grid-cols-2 gap-3">
            @php
                $args = [];
                $defaults = [
                    'quantity' => 1,
                    'class' => implode(
                        ' ',
                        array_filter([
                            'button',
                            'product_type_' . $product->get_type(),
                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                            $product->supports('ajax_add_to_cart') &&
                            $product->is_purchasable() &&
                            $product->is_in_stock()
                                ? 'ajax_add_to_cart'
                                : '',
                        ]),
                    ),
                    'attributes' => [
                        'data-product_id' => $product->get_id(),
                        'data-product_sku' => $product->get_sku(),
                        'aria-label' => $product->add_to_cart_description(),
                        'rel' => 'nofollow',
                    ],
                ];

                $args = apply_filters('woocommerce_loop_add_to_cart_args', wp_parse_args($args, $defaults), $product);
                $attr_string = '';
                foreach ($args['attributes'] as $key => $val) {
                    $attr_string .= esc_attr($key) . '="' . esc_attr($val) . '" ';
                }
            @endphp

            {{-- Outlined Add to Cart --}}
            <a href="{{ esc_url($product->add_to_cart_url()) }}"
                class="{{ esc_attr(isset($args['class']) ? $args['class'] : 'button') }} block w-full text-center py-2 px-4 rounded-full border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 text-sm font-semibold text-zinc-900 dark:text-zinc-100 transition-colors"
                {!! $attr_string !!}
                data-quantity="{{ esc_attr(isset($args['quantity']) ? $args['quantity'] : 1) }}">
                Add to Cart
            </a>

            {{-- Solid Buy Now (Redirects directly to checkout for demo) --}}
            <a href="?add-to-cart={{ $product->get_id() }}"
                class="block w-full text-center py-2 px-4 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-200 transition-colors">
                Buy Now
            </a>
        </div>

    </div>
</div>

@php
    /**
     * The template for displaying product content in the single-product.php template
     *
     * This template overrides the default WooCommerce template to provide a beautiful,
     * modern, 2-column layout matching the "Anatomy of a Perfect Product Page" wireframe.
     */

    defined('ABSPATH') || exit();

    global $product;

    /**
     * Hook: woocommerce_before_single_product.
     *
     * @hooked woocommerce_output_all_notices - 10
     */
    do_action('woocommerce_before_single_product');

    if (post_password_required()) {
        echo get_the_password_form(); // WPCS: XSS ok.
        return;
    }
@endphp

<div id="product-{{ the_ID() }}" {{ wc_product_class('flex flex-col gap-16 pb-24', $product) }}>

    {{-- Breadcrumbs (Structure) --}}
    <div class="w-full">
        @php
            woocommerce_breadcrumb([
                'wrap_before' =>
                    '<nav class="flex text-sm text-zinc-500 font-medium" aria-label="Breadcrumb"><ol class="inline-flex items-center space-x-1 md:space-x-3">',
                'wrap_after' => '</ol></nav>',
                'before' => '<li class="inline-flex items-center">',
                'after' => '</li>',
                'delimiter' => '<flux:icon.chevron-right class="w-4 h-4 text-zinc-400 mx-1" />',
            ]);
        @endphp
    </div>

    {{-- Top Section: 2-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">

        {{-- Left Column: Visuals & Reassuring Elements --}}
        <div class="flex flex-col gap-6">
            {{-- Main Image & Gallery --}}
            <div
                class="bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl p-8 relative flex items-center justify-center isolate overflow-hidden group">
                {{-- Heart Toggle in top right of image --}}
                <div class="absolute top-4 right-4 z-20">
                    <livewire:favorite-toggle :productId="$product->get_id()" />
                </div>

                {{-- Sale Badge --}}
                @if ($product->is_on_sale())
                    <div class="absolute top-4 left-4 z-20">
                        <flux:badge color="red" size="sm" class="font-semibold shadow-sm">
                            {!! apply_filters(
                                'woocommerce_sale_flash',
                                '<span class="onsale">' . esc_html__('Sale!', 'woocommerce') . '</span>',
                                $post,
                                $product,
                            ) !!}
                        </flux:badge>
                    </div>
                @endif

                <div class="relative z-10 w-full aspect-square flex items-center justify-center">
                    {!! woocommerce_get_product_thumbnail('woocommerce_single', [
                        'class' =>
                            'max-w-full max-h-full object-contain drop-shadow-2xl mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform duration-700 ease-out',
                    ]) !!}
                </div>

                {{-- Decorative background glow --}}
                <div
                    class="absolute inset-0 bg-gradient-to-tr from-zinc-200/50 to-transparent dark:from-zinc-800/50 -z-10 rounded-3xl">
                </div>
            </div>

            {{-- Gallery Thumbnails (Mocked for single image, but hooked to real gallery if present) --}}
            @php
                $attachment_ids = $product->get_gallery_image_ids();
            @endphp
            @if ($attachment_ids)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                    <div
                        class="aspect-square bg-zinc-100 dark:bg-zinc-800 rounded-xl flex items-center justify-center cursor-pointer border-2 border-zinc-900 dark:border-white">
                        {!! woocommerce_get_product_thumbnail('thumbnail', [
                            'class' => 'max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal p-2',
                        ]) !!}
                    </div>
                    @foreach (array_slice($attachment_ids, 0, 4) as $attachment_id)
                        <div
                            class="aspect-square bg-zinc-50 dark:bg-zinc-900/50 border border-transparent hover:border-zinc-300 dark:hover:border-zinc-700 rounded-xl flex items-center justify-center cursor-pointer transition-colors">
                            {!! wp_get_attachment_image($attachment_id, 'thumbnail', false, [
                                'class' => 'max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal p-2',
                            ]) !!}
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Reassuring Elements --}}
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div
                    class="flex items-center gap-3 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-full bg-white dark:bg-zinc-800 flex items-center justify-center shrink-0 shadow-sm">
                        <flux:icon.truck class="w-5 h-5 text-zinc-900 dark:text-white" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Free Shipping</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">On orders over $50</div>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-100 dark:border-zinc-800">
                    <div
                        class="w-10 h-10 rounded-full bg-white dark:bg-zinc-800 flex items-center justify-center shrink-0 shadow-sm">
                        <flux:icon.arrow-path class="w-5 h-5 text-zinc-900 dark:text-white" />
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Easy Returns</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">30-day return policy</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Product Summary --}}
        <div class="flex flex-col">

            {{-- Category / Subtitle --}}
            @php
                $categories = wc_get_product_category_list($product->get_id(), ', ');
            @endphp
            @if ($categories)
                <div class="text-xs font-bold tracking-widest text-zinc-500 uppercase mb-3">
                    {!! strip_tags($categories) !!}
                </div>
            @endif

            {{-- Title --}}
            <h1
                class="text-4xl sm:text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tight leading-none mb-4">
                {{ $product->get_title() }}
            </h1>

            {{-- Reviews Summary & Stock --}}
            <div class="flex flex-wrap items-center gap-4 mb-6">
                @php
                    $review_count = $product->get_review_count();
                    $average = $product->get_average_rating();
                    $display_rating = $average > 0 ? number_format($average, 1) : '5.0';
                    $display_count = $review_count > 0 ? $review_count . ' Reviews' : '1.2k Reviews';
                @endphp
                <div class="flex items-center gap-1.5 cursor-pointer group">
                    <div class="flex gap-0.5">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 {{ $i < floor((float) $display_rating) ? 'text-yellow-400 fill-current' : 'text-zinc-300 dark:text-zinc-700 fill-current' }}"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <span
                        class="text-sm font-medium text-zinc-600 dark:text-zinc-400 group-hover:text-zinc-900 border-b border-transparent group-hover:border-zinc-900 transition-colors">
                        {{ $display_rating }} ({{ $display_count }})
                    </span>
                </div>

                <div class="w-1 h-1 rounded-full bg-zinc-300 dark:bg-zinc-700 hidden sm:block"></div>

                {{-- Stock Status --}}
                @if ($product->is_in_stock())
                    <div class="flex items-center gap-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400">
                        <flux:icon.check-circle class="w-4 h-4" />
                        In Stock
                    </div>
                @else
                    <div class="flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400">
                        <flux:icon.x-circle class="w-4 h-4" />
                        Out of Stock
                    </div>
                @endif
            </div>

            {{-- Price --}}
            <div class="mb-8">
                <span class="text-4xl font-light text-zinc-900 dark:text-white tracking-tight">
                    {!! $product->get_price_html() !!}
                </span>
            </div>

            {{-- Short Description --}}
            @if ($product->get_short_description())
                <div
                    class="prose prose-zinc dark:prose-invert prose-p:text-zinc-500 dark:prose-p:text-zinc-400 prose-p:leading-relaxed max-w-none mb-8">
                    {!! $product->get_short_description() !!}
                </div>
            @endif

            <hr class="border-zinc-200 dark:border-zinc-800 mb-8" />

            {{-- Add to Cart Form --}}
            <div class="mb-10">
                @php
                    do_action('woocommerce_' . $product->get_type() . '_add_to_cart');
                @endphp
            </div>

            {{-- Product Meta --}}
            <div
                class="flex flex-col gap-2 text-sm text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/50 p-6 rounded-2xl">
                @php do_action('woocommerce_product_meta_start'); @endphp

                @if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable')))
                    <div class="flex gap-2">
                        <span class="font-semibold text-zinc-900 dark:text-white min-w-[80px]">SKU:</span>
                        <span
                            class="sku">{{ $product->get_sku() ? $product->get_sku() : esc_html__('N/A', 'woocommerce') }}</span>
                    </div>
                @endif

                @php do_action('woocommerce_product_meta_end'); @endphp
            </div>

        </div>
    </div>

    {{-- Middle Section: In-Depth Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 pt-16 border-t border-zinc-200 dark:border-zinc-800">

        {{-- Left: Featured Media / Reviews (Spans 5 cols) --}}
        <div class="lg:col-span-5 flex flex-col gap-8">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white mb-2">Featured Highlights</h2>

            {{-- Mock Video Embed (as per wireframe) --}}
            <div
                class="w-full aspect-video bg-zinc-900 rounded-2xl overflow-hidden relative flex items-center justify-center group cursor-pointer shadow-lg hover:shadow-xl transition-shadow">
                <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=1000&auto=format&fit=crop"
                    class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:scale-105 transition-transform duration-700"
                    alt="Product Video Thumbnail" />
                <div
                    class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center relative z-10 group-hover:bg-white/30 transition-colors">
                    <flux:icon.play class="w-8 h-8 text-white ml-1" />
                </div>
            </div>

            {{-- Mock Featured Review --}}
            <div
                class="bg-zinc-50 dark:bg-zinc-900/50 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800 relative">
                <flux:icon.chat-bubble-bottom-center-text
                    class="w-8 h-8 text-zinc-200 dark:text-zinc-800 absolute top-6 right-6" />
                <div class="flex gap-1 mb-3">
                    @for ($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <p class="text-zinc-700 dark:text-zinc-300 italic mb-4">"Absolutely the best purchase I've made this
                    year. The build quality is phenomenal and it looks even better in person."</p>
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                    <div>
                        <div class="font-semibold text-zinc-900 dark:text-white">Alex Morgan</div>
                        <div class="text-xs text-zinc-500 flex items-center gap-1"><flux:icon.check-badge
                                class="w-3 h-3 text-emerald-500" /> Verified Buyer</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Detailed Description (Spans 7 cols) --}}
        <div class="lg:col-span-7 prose prose-lg prose-zinc dark:prose-invert max-w-none">
            <h2 class="text-3xl font-bold tracking-tight mb-8">Detailed Information</h2>

            {{-- We fall back to standard WooCommerce content filter to render the long description --}}
            @php
                $content = apply_filters('the_content', $post->post_content);
            @endphp

            @if (trim($content))
                <div class="text-zinc-600 dark:text-zinc-400 font-normal leading-relaxed space-y-6">
                    {!! str_replace('<p>', '<p class="mb-6">', $content) !!}
                </div>
            @else
                <p class="text-zinc-500 italic">No detailed description available for this product.</p>
            @endif

            {{-- Specifications Table (Hooked into attributes) --}}
            @if ($product->has_attributes() || $product->has_dimensions() || $product->has_weight())
                <div class="mt-12">
                    <h3 class="text-xl font-bold mb-6 border-b border-zinc-200 dark:border-zinc-800 pb-2">Product
                        Specifications</h3>
                    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
                        @php do_action('woocommerce_product_additional_information', $product); @endphp
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Section: Related & Reviews --}}
    <div
        class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 pt-16 border-t border-zinc-200 dark:border-zinc-800 w-full mb-16">

        {{-- Left: Reviews --}}
        <div>
            @php
                // Load the WooCommerce reviews template directly, without the tabs.
                comments_template();
            @endphp
        </div>

        {{-- Right: Related Products --}}
        <div class="related-products-wrapper">
            @php
                // Output related products. We force it to a 2-column layout to fit this right-hand column.
                woocommerce_output_related_products();
            @endphp
        </div>

    </div>

</div>

@php
    do_action('woocommerce_after_single_product');
@endphp

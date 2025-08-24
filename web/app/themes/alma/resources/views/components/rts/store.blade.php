@push('js')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                async add(id, element) {
                    try {
                        const response = await fetch(wc_add_to_cart_params.ajax_url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'woocommerce_add_to_cart',
                                product_id: id,
                                quantity: 1
                            })
                        });

                        const data = await response.json();

                        if (data.fragments && data.cart_hash) {
                            // Ensure element is a jQuery object
                            const $button = jQuery(element);
                            // Trigger WooCommerce cart update
                            jQuery(document.body).trigger('added_to_cart', [data.fragments, data
                                .cart_hash, $button
                            ]);
                            return true;
                        } else {
                            console.error('Error adding to cart:', data.error || 'Unknown error');
                            return false;
                        }
                    } catch (error) {
                        console.error('Cart add error:', error);
                        return false;
                    }
                }
            });

            Alpine.store('share', {
                share(title, permalink) {
                    if (navigator.share) {
                        navigator.share({
                            title: title,
                            url: permalink
                        }).catch(error => console.error('Share error:', error));
                    } else {
                        // Fallback for browsers that don't support Web Share API
                        navigator.clipboard.writeText(permalink)
                            .then(() => alert('Se ha copiado el enlace al portapapeles!'))
                            .catch(error => console.error('Clipboard error:', error));
                    }
                }
            });
        });
    </script>
@endpush

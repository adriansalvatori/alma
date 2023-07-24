import Alpine from "alpinejs";

export const Store = {
  setup() {
    if (window.wc_add_to_cart_params) {
      Alpine.store('cart', {
        view_cart_text: window.wc_add_to_cart_params.i18n_view_cart,
        cart_url: window.wc_add_to_cart_params.cart_url,
        add(ProductID, element) {
          element.classList.toggle('is-loading');
          fetch(`${this.cart_url}?add-to-cart=${ProductID}`)
            .then(() => {
              element.classList.toggle('is-loading')
            })
            .catch(err => console.error(err));
        },
      });
    }


    Alpine.store('share', {
      share(title, url) {
        if (navigator && navigator.canShare) {
          // Web Share API is supported
          navigator.share({
              title: title,
              url: url,
            }).then(() => {
              navigator.vibrate(200);
              console.log('Shared successfully.');
            })
            .catch(console.error);
        } else {
          // We'll have to use a fallback

        }
      },
    })
  },
}

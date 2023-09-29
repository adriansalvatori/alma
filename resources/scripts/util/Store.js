import Alpine from "alpinejs";

export const Store = {
  setup() {
    if (window.wc_add_to_cart_params) {
      Alpine.store('interview', {
        start() {
          const video = document.getElementById('video');
          navigator.mediaDevices.getUserMedia({
              video: true
          })
          .then((stream) => {
              video.srcObject = stream;
              video.play();
              video.style.transform = 'scaleX(-1)';
          });
        },
        stop() {
            const video = document.getElementById('video');
            const stream = video.srcObject;
            const tracks = stream.getTracks();
            tracks.forEach(function(track) {
                track.stop();
            });
            video.srcObject = null;
        }
      })
      
      Alpine.store('external', {
        modify(el, data_value, new_value){
          console.log(el, data_value, new_value)
          document.querySelector(el)._x_dataStack[0][data_value] = new_value;
        },
        reduce_filename($event){
          return $event.target.files[0].name.length > 21 ? $event.target.files[0].name.substring(0, 21) + '...' : $event.target.files[0].name
        }
      })
  
      Alpine.store('form', {
        validate(context){
          for (const el of context.querySelectorAll("[required]")) {
            if (!el.reportValidity()) {
              return false;
            }
          }
          return true;
        }
      })
  
      Alpine.store('image', {
        async remove_background(image_src, element){
          const image_container = document.querySelector(element)
          image_container.querySelector('figure').classList.add('is-blurred');
          const options = {
            // publicPath: publicPath.href,
            debug: true,
            progress: (key, current, total) => {
              const [type, subtype] = key.split(':');
              const notice = `${(
                (current / total) *
                100
              ).toFixed(0)}%`;
              image_container.querySelector('.text-info').textContent = `Espera mientras procesamos tu imagen (${notice})`;
            },
            fetchArgs: {
              mode: 'no-cors'
            }
          }
  
          const blob = await imglyRemoveBackground(image_src, options);
          const url = URL.createObjectURL(blob);
          image_container.querySelector('figure').classList.remove('is-blurred');
          image_container.querySelector('.text-info').textContent = '';
          return url;
        }
      })
      
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

import AOS from 'aos';
import 'aos/dist/aos.css';
import feather from 'feather-icons';
import $ from 'jquery';
import {jarallax, jarallaxVideo} from 'jarallax';
import Bulma from '@vizuaalog/bulmajs';
import 'slick-carousel/slick/slick';

export default {
  init() {
    AOS.init()
    feather.replace()
    jarallaxVideo()

    /** Bulma 
     * @docs https://bulmajs.tomerbe.co.uk/docs/0.11/1-getting-started/1-introduction/
     */
    

    /**Parallax */

    jarallax(document.querySelectorAll('.is-parallax-contain'), {
      speed: 0.9,
      imgSize: 'cover',
      imgPosition: '25% 50%',
    })

    jarallax(document.querySelectorAll('.is-parallax-cover'), {
      speed: 0.4,
      imgSize: 'cover',
      imgPosition: '25% 50%',
    })

    document.querySelectorAll('.is-parallax-video').forEach(element => {
      jarallax(element, {
        speed: 0.4,
        videoSrc: `mp4:${element.dataset.url}`
      });
    });

    /**Slider */
    $('.slick-carousel').slick({
      dots: true,
      infinite: true,
      speed: 400,
      slidesToShow: 4,
      slidesToScroll: 4,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3,
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2
          }
        },
        {
          breakpoint: 600,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1
          }
        }
      ]
    });

  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};

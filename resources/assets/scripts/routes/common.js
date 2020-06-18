import AOS from 'aos'
import {tns} from 'tiny-slider/src/tiny-slider'
import feather from 'feather-icons'
import {jarallax, jarallaxVideo} from 'jarallax'
import Bulma from '@vizuaalog/bulmajs'
export default {
  init() {
    AOS.init()
    console.log(Bulma)
    feather.replace()
    jarallaxVideo()

    /** Bulma 
     * @docs https://bulmajs.tomerbe.co.uk/docs/0.11/1-getting-started/1-introduction/
     */

    /** Filter */

    let filterSelects = document.querySelectorAll('select[data-filter-type]')

    function setFilterParameters(){
      let query = ''
      filterSelects.forEach(select => {
        query = query +' '+ select.value
      })
      console.log(query)
      document.querySelector('#filterbox').value = query
    }

    filterSelects.forEach(select => {
      select.addEventListener('change', function(){
        setFilterParameters()
      })
    })

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

    jarallax(document.querySelectorAll('.is-parallax-video'), {
      speed: 0.4,
      videoSrc: 'mp4:/app/uploads/2020/06/4514359.mp4'
    });



    /**Slider */

    if (document.querySelector('.carousel')) {
      new tns({
        container: '.carousel',
        items: 2,
        slideBy: 'page',
        autoplay: false,
        mouseDrag: true,
        autoplayHoverPause:true,
        controls: false,
        pagination: false,
        nav:false,
        autoplayButtonOutput: false,
      })
    }
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};

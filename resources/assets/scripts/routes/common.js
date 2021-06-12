import AOS from 'aos';
import 'aos/dist/aos.css';
import feather from 'feather-icons';
import {jarallax, jarallaxVideo} from 'jarallax';
import {locomo} from './locomotive-gsap';
import {slider} from '../util/slider';
import {mailer} from '../util/mail';
import {add_to_cart} from '../util/domUtils'

export default {
  init() {
    AOS.init()
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
    
    slider.type1({
      element : '.slider',
      props : {
        type       : 'loop',
        perPage    : 3,
        perMove    : 3,
        pagination : false,
        speed      : 500
      }
    }, true)
    

    let data = {
      formElement: '.contact-form',
      action : 'send_mailsinador',
      to : ['emyerbetharenas@gmail.com', 'emyerbetharenas@gmail.com'],
      subject : 'Este es el asunto del vta',
      error : 'No se pudo enviar el correo',
      success : 'Se envio el correo super bien',
      spanBlock : true,
      files : [
        {
          type : 'jpg',
          size : 2
        },
        {
          type : 'png',
          size : 2
        },
        {
          type : 'pdf',
          size : 2
        }
      ]
    }

    mailer(data, (response)=>{
      console.log(response)
    })

    add_to_cart('.add-cartinador', (response)=>{
      console.log(response)
    })

  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
    window.scrollTo(0, 0)
    locomo()

    feather.replace()
    setTimeout(()=>{
      document.querySelector('.preloader').classList.add('close')
    }, 1500)
  },
};

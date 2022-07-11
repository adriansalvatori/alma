import { jarallax, jarallaxVideo } from "jarallax";
import LocomotiveScroll from 'locomotive-scroll';

export const Inertia = {
  init: function(){

    const scroll = new LocomotiveScroll({
      el: document.querySelector('[data-inertia-container]'),
      name: 'inertia',
      repeat: true,
      offset: ['10%','10%'],
      smooth: true,
      getDirection: true,
      getSpeed: true,
    });


    jarallaxVideo();
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

    console.log('Inertia Initiated')
  }
}

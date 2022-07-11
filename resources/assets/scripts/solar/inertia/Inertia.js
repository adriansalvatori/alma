import { jarallax, jarallaxVideo } from "jarallax";
import LocomotiveScroll from 'locomotive-scroll';
import barba from '@barba/core'
import gsap from "gsap";
import feather from 'feather-icons';

const JarallaxSetup = () => {
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
      speed: 0.8,
      videoSrc: `mp4:${element.dataset.url}`
    });
  });
}

const ScrollSetup = () => {
  const scroll = new LocomotiveScroll({
    el: document.querySelector('[data-inertia-container]'),
    name: 'inertia',
    repeat: false,
    offset: ['0%','0%'],
    smooth: true,
    getDirection: true,
    getSpeed: true,
    class: 'is-revealed'
  });

  const barbaConfig = {
    schema: {
      prefix: 'data-solar'
    },
    transitions:[{
      name: 'fade-orbit',
      leave(data) {
        return gsap.to(data.current.container, {
          opacity: 0
        });
      },
      after() {
        JarallaxSetup();
        feather.replace();
        scroll.update();
        scroll.scrollTo('top');
      },
      enter(data) {
        return gsap.from(data.next.container, {
          opacity: 0
        });
      }
    }]
  }

  barba.init(barbaConfig);
}

export const Inertia = {
  init: () => {
    ScrollSetup();
    JarallaxSetup();
    feather.replace();

    console.log('Inertia Initiated')
  }
}

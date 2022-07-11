import { jarallax, jarallaxVideo } from "jarallax";
import LocomotiveScroll from 'locomotive-scroll';
import gsap from "gsap";

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
      speed: 0.4,
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
}

export const Inertia = {
  init: () => {
    ScrollSetup();
    JarallaxSetup();
    //RevealSetup();

    console.log('Inertia Initiated')
  }
}

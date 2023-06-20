import { jarallax, jarallaxVideo } from "jarallax";
import LocomotiveScroll from 'locomotive-scroll';
import barba from '@barba/core';
import Gravity from "../gravity/Gravity";
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
      videoSrc: `mp4:${element.dataset.url}`,
    });
  });
}

const Fade = (target, step, direction) => {
  const config = {
    duration: step === 'leave' ? 1 : 1.5,
    ease: "SlowMo.easeNone",
    yPercent: step === 'leave' ? 10 : -10,
    opacity: 0,
  }

  if(direction === 'right'){
    config.xPercent = step === 'leave' ? -100 : 100
  }

  return step === 'leave' ? gsap.to(target, config) : gsap.from(target, config)
}

const Swipe = (target, step, direction) => {

  const config = {
    duration: step === 'leave' ? 1 : 1.5,
    ease: "SlowMo.easeNone",
    xPercent: step === 'leave' ? 100 : -100,
    opacity: 0,
  }

  if(direction === 'right'){
    config.xPercent = step === 'leave' ? -100 : 100
  }

  return step === 'leave' ? gsap.to(target, config) : gsap.from(target, config)

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
    class: 'is-revealed',
  });

  const move = (context) => {
    if(context.id === 'continue-fulfilling') {
      context.el.click();
      context.id = 'continue-fulfilled';
    } else if (context.id != 'continue-fulfilled'){
      context.id ='continue-fulfilling'
    }
  }

  window.onwheel = (wheel) => {
    const args = scroll.scroll;
    if( args.currentElements['continue']
    && args.currentElements['continue'].id != 'continue-fulfilled'
    && document.querySelector('html').classList.contains('has-scroll-scrolling')
    && !document.querySelector('#app').classList.contains('is-animating')){
      const context = args.currentElements['continue']
      if(wheel.movementY === 0){
        if(context.el.dataset.direction === 'right' && wheel.deltaY > 0) move(context);
        if(context.el.dataset.direction === 'left' && wheel.deltaY < 0) move(context);
      }
    }
  }

  barba.hooks.before(() => {
    barba.wrapper.classList.add('is-animating');
  });
  barba.hooks.beforeEnter(() => {
    JarallaxSetup();
    feather.replace({
      class: 'feather',
      'stroke-width': 1.5,
    });
  })
  barba.hooks.after(() => {
    scroll.destroy();
    barba.wrapper.classList.remove('is-animating');
    document.querySelectorAll('[data-gravity]')
    .forEach(element => new Gravity(element, {
      y: 0.3, // horizontal
      x: 0.3, // vertical
      s: 0.2, // speed
      rs: 0.7, // release speed
    }));
    scroll.init();
  })

  const barbaConfig = {
    debug: true,
    timeout: 15000,
    schema: {
      prefix: 'data-solar',
    },
    transitions:[{
      name: 'swipe-orbit-right',
      sync: true,
      custom: ({ trigger }) => trigger.dataset && trigger.dataset.direction === 'right',
      leave(data) {
        return Swipe(data.current.container, 'leave', 'right')
      },
      enter(data) {
        return Swipe(data.next.container, 'enter', 'right')
      },
    },{
      name: 'swipe-orbit-left',
      sync: true,
      custom: ({ trigger }) => trigger.dataset && trigger.dataset.direction === 'left',
      leave(data) {
        return Swipe(data.current.container, 'leave', 'left')
      },
      enter(data) {
        return Swipe(data.next.container, 'enter', 'left')
      },
    },{
      name: 'fade-orbit',
      sync: true,
      custom: ({ trigger }) => !trigger.dataset.direction,
      leave(data) {
        return Fade(data.current.container, 'leave', 'left')
      },
      enter(data) {
        return Fade(data.next.container, 'enter', 'left')
      },
    }],
  }

  barba.init(barbaConfig);
}

export const Inertia = {
  init: () => {
    ScrollSetup();
    JarallaxSetup();
    feather.replace({
      class: 'feather',
      'stroke-width': 1.5,
    });
  },
}

import { gsap } from 'gsap';
import { SplitText } from 'gsap/SplitText';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger, SplitText);

export const Reveal = {
  init() {
    console.log("gsap reveal started");
    document.querySelectorAll('[data-reveal], [data-reveal-text]').forEach(el => {
      let delay = parseInt(el.getAttribute('data-reveal-delay'), 10) || 0;
      if (el.hasAttribute('data-reveal-text')) {
        let split = SplitText.create(el, { type: 'words, chars' });
        gsap.from(split.chars, {
          scrollTrigger: {
            trigger: el,
            start: 'top 80%',
            toggleActions: 'play none none none',
          },
          duration: 1,
          y: 100,
          autoAlpha: 0,
          stagger: 0.05,
          clipPath: 'inset( 100% 0 100% 0 )',
          delay: delay / 1000,
        });
      } else {
        gsap.from(el, {
          scrollTrigger: {
            trigger: el,
            start: 'top 80%',
            toggleActions: 'play none none none',
          },
          duration: 1,
          y: 100,
          autoAlpha: 0,
          clipPath: 'inset( 0 0 100% 0 )',
          delay: delay / 1000,
        });
      }
    });
  },
};

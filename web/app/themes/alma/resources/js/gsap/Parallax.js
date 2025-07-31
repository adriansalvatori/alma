import { jarallax, jarallaxVideo } from 'jarallax';

export const Parallax = {
  init() {
    jarallaxVideo();
    console.log('parallax started');

    // Use data-parallax-type attribute for type selection
    const elements = document.querySelectorAll('[data-parallax]');
    elements.forEach((el) => {
      const type = el.getAttribute('data-parallax') || 'scroll';
      jarallax(el, {
        speed: 0.6,
        type,
      });
    });
  },
};

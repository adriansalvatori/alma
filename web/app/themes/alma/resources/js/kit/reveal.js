import { gsap } from 'gsap';
import { SplitText } from 'gsap/SplitText';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger, SplitText);

/**
 * Scroll reveals: [data-reveal] rises in, [data-reveal-text] letter by letter, after
 * data-reveal-delay milliseconds. Something already on screen plays at once; the rest waits for
 * its top to reach 80% of the viewport. Each element is set up once (splitting a heading twice
 * nests its letters), and triggers whose element left the page are dropped. Opacity, not
 * visibility, so unrevealed headings stay in the accessibility tree.
 */
export function startReveals() {
  ScrollTrigger.getAll().filter((trigger) => trigger.trigger && !trigger.trigger.isConnected).forEach((trigger) => trigger.kill());

  const still = matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('[data-reveal]:not([data-revealed]), [data-reveal-text]:not([data-revealed])').forEach((el) => {
    el.setAttribute('data-revealed', '');

    if (still) {
      return;
    }

    const delay = (parseInt(el.dataset.revealDelay, 10) || 0) / 1000;
    const scrollTrigger = el.getBoundingClientRect().top < innerHeight ? undefined : { trigger: el, start: 'top 80%', toggleActions: 'play none none none' };

    if (el.hasAttribute('data-reveal-text')) {
      const split = SplitText.create(el, { type: 'words, chars' });
      gsap.from(split.chars, { scrollTrigger, delay, duration: 1, y: 100, opacity: 0, stagger: 0.05, clipPath: 'inset(100% 0 100% 0)' });
    } else {
      gsap.from(el, { scrollTrigger, delay, duration: 1, y: 100, opacity: 0, clipPath: 'inset(0 0 100% 0)' });
    }
  });
}

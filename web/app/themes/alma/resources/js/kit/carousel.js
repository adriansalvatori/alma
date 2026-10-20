import { gsap } from 'gsap';

const lerp = (from, to, t) => (1 - t) * from + t * to;
const clamp = (value, min, max) => Math.max(min, Math.min(value, max));

/**
 * <x-kit.carousel>: drag the track (mouse or touch); it eases to where you let go and the cards
 * squash with speed. A drag that ends over a card doesn't count as a click on it.
 */
class Carousel {
  constructor(el) {
    this.el = el;
    this.track = el.querySelector('.kit-carousel-track');
    this.items = [...el.querySelectorAll('.kit-carousel-item')];
    Object.assign(this, { progress: 0, x: 0, oldX: 0, speed: 0, dragging: false, distance: 0, startX: 0, frame: 0 });
    ['measure', 'down', 'move', 'up', 'tick'].forEach((name) => { this[name] = this[name].bind(this); });

    addEventListener('resize', this.measure);
    el.addEventListener('mousedown', this.down);
    el.addEventListener('touchstart', this.down, { passive: true });
    addEventListener('mousemove', this.move, { passive: true });
    addEventListener('touchmove', this.move, { passive: true });
    addEventListener('mouseup', this.up);
    addEventListener('touchend', this.up, { passive: true });

    this.measure();
    this.tick();
  }

  measure() {
    const last = this.items.at(-1);
    this.max = last ? Math.max(0, last.offsetLeft + last.offsetWidth - this.el.clientWidth + parseFloat(getComputedStyle(this.el).paddingLeft)) : 0;
  }

  point(event) {
    return event.touches?.[0]?.clientX ?? event.clientX;
  }

  down(event) {
    if (event.type === 'mousedown') {
      event.preventDefault();
    }

    this.dragging = true;
    this.distance = 0;
    this.startX = this.point(event);
    this.el.classList.add('is-dragging');
  }

  move(event) {
    const x = this.point(event);

    if (!this.dragging || x === undefined) {
      return;
    }

    this.distance += Math.abs(this.startX - x);
    this.progress = clamp(this.progress + (this.startX - x) * 2.5, 0, this.max);
    this.startX = x;
  }

  up() {
    if (!this.dragging) {
      return;
    }

    this.dragging = false;
    this.el.classList.remove('is-dragging');

    if (this.distance > 6) {
      const swallow = (event) => { event.preventDefault(); event.stopPropagation(); };
      this.el.addEventListener('click', swallow, { capture: true, once: true });
      setTimeout(() => this.el.removeEventListener('click', swallow, { capture: true }), 0);
    }
  }

  tick() {
    this.frame = requestAnimationFrame(this.tick);
    this.x = lerp(this.x, this.progress, 0.1);
    this.track.style.transform = `translate3d(${-this.x}px, 0, 0)`;

    if (this.speed !== this.oldX - this.x) {
      this.speed = Math.min(100, this.oldX - this.x);
      this.oldX = this.x;
      gsap.to(this.items, { scale: 1 - Math.abs(this.speed) * 0.002, duration: 0.1, overwrite: true });
    }
  }

  destroy() {
    cancelAnimationFrame(this.frame);
    removeEventListener('resize', this.measure);
    removeEventListener('mousemove', this.move);
    removeEventListener('touchmove', this.move);
    removeEventListener('mouseup', this.up);
    removeEventListener('touchend', this.up);
  }
}

let running = [];

/** Starts the page's carousels once each; carousels that left the page stop. */
export function startCarousels() {
  running.filter((carousel) => !carousel.el.isConnected).forEach((carousel) => carousel.destroy());
  running = running.filter((carousel) => carousel.el.isConnected);

  document.querySelectorAll('.kit-carousel').forEach((el) => {
    if (!running.some((carousel) => carousel.el === el)) {
      running.push(new Carousel(el));
    }
  });
}

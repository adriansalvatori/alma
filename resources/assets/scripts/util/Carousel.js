import { gsap } from 'gsap';

const lerp = (f0, f1, t) => (1 - t) * f0 + t * f1;
const clamp = (val, min, max) => Math.max(min, Math.min(val, max));

class DragScroll {
  constructor(obj, $el) {
    this.$el = $el;
    this.$wrap = this.$el.querySelector(obj.wrap);
    this.$items = Array.from(this.$el.querySelectorAll(obj.item));
    this.init();
  }

  init() {
    this.progress = 0;
    this.speed = 0;
    this.oldX = 0;
    this.x = 0;
    this.playrate = 0;
    this.isDragging = false;
    //
    this.bindings();
    this.events();
    this.calculate();
    this.raf();
  }

  bindings() {
    [
      'events',
      'calculate',
      'raf',
      'handleWheel',
      'move',
      'handleTouchStart',
      'handleTouchMove',
      'handleTouchEnd',
      'destroy',
    ].forEach((i) => {
      this[i] = this[i].bind(this);
    });
  }

  calculate() {
    if (!this.$items.length) return;
    this.progress = 0;
    this.wrapWidth = this.$items[0].clientWidth * this.$items.length;
    this.$wrap.style.width = `${this.wrapWidth}px`;
    this.maxScroll = this.wrapWidth - this.$el.clientWidth + (this.$items[0].clientWidth * 2); // Add some space at the end
  }

  handleWheel(e) {
    this.progress += e.deltaY;
    this.move();
  }

  handleTouchStart(e) {
    e.preventDefault();
    window.scroll.stop(); // Disable locomotive scroll
    this.isDragging = true;
    this.startX = e.clientX || e.touches[0].clientX;
    this.$el.classList.add('dragging');
  }

  handleTouchMove(e) {
    if (!this.isDragging) return false;
    const x = e.clientX || (e.touches[0] && e.touches[0].clientX); // Check if e.touches[0] exists
    if (typeof x === 'undefined') return; // Return if x is undefined
    this.progress += (this.startX - x) * 2.5;
    this.startX = x;
    this.move();
  }

  handleTouchEnd() {
    if (this.isDragging) {
      window.scroll.start(); // Restart locomotive scroll
      this.isDragging = false;
      this.$el.classList.remove('dragging');
    }
  }

  move() {
    this.progress = clamp(this.progress, 0, this.maxScroll);
  }

  events() {
    window.addEventListener('resize', this.calculate);
    // window.addEventListener('wheel', this.handleWheel, { passive: true });
    //
    this.$el.addEventListener('touchstart', this.handleTouchStart, {
      passive: true,
    });
    window.addEventListener('touchmove', this.handleTouchMove, {
      passive: true,
    });
    window.addEventListener('touchend', this.handleTouchEnd, {
      passive: true,
    });
    //
    this.$el.addEventListener('mousedown', this.handleTouchStart);
    window.addEventListener('mousemove', this.handleTouchMove, {
      passive: true,
    });
    window.addEventListener('mouseup', this.handleTouchEnd);
    document.body.addEventListener('mouseleave', this.handleTouchEnd);
  }

  raf() {
    this.x = lerp(this.x, this.progress, 0.1);
    this.playrate = this.x / this.maxScroll;
    //
    this.$wrap.style.transform = `translate3d(${-this.x}px, 0, 0)`;
    //
    if (this.speed !== this.oldX - this.x) {
      this.speed = Math.min(100, this.oldX - this.x);
      this.oldX = this.x;
      this.$items.forEach((item) => {
        const scale = 1 - Math.abs(this.speed) * 0.002;
        gsap.to(item, { scale: scale, duration: 0.3, overwrite: true });
      });
    }
  }

  destroy() {
    window.removeEventListener('resize', this.calculate);
    // window.removeEventListener('wheel', this.handleWheel);
    //
    this.$el.removeEventListener('touchstart', this.handleTouchStart);
    window.removeEventListener('touchmove', this.handleTouchMove);
    window.removeEventListener('touchend', this.handleTouchEnd);
    //
    this.$el.removeEventListener('mousedown', this.handleTouchStart);
    window.removeEventListener('mousemove', this.handleTouchMove);
    window.removeEventListener('mouseup', this.handleTouchEnd);
    document.body.removeEventListener('mouseleave', this.handleTouchEnd);
  }
}

export const Carousel = {
  init() {
    /*--------------------
    Instances
    --------------------*/
    document.querySelectorAll('.carousel').forEach(($el) => {
      const scroll = new DragScroll(
        {
          wrap: '.carousel-wrapper',
          item: '.carousel-item',
        },
        $el
      );

      /*--------------------
      One raf to rule em all
      --------------------*/
      const raf = () => {
        requestAnimationFrame(raf);
        scroll.raf();
      };
      raf();
    });
  },
};

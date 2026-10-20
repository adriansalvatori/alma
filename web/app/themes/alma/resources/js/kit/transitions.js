import { gsap } from 'gsap';

/**
 * Page transitions on wire:navigate (PAGE_TRANSITIONS, with WIRE_NAVIGATE):
 *
 * - Following a [data-morph] link (<x-kit.media-card>): its [data-morph-media] panel lifts out of
 *   the page and grows to fill the screen, the page swaps under it, and it dissolves into the new
 *   page once its [data-morph-target] image (if any) has decoded.
 * - Any other navigation: the curtain (--kit-curtain) grows as a circle from the click, the page
 *   swaps, and it collapses into [data-curtain-anchor] (or the top-right corner).
 *
 * Back and forward are left to Livewire. The overlays live on <html>, outside the <body> that
 * wire:navigate swaps. With reduced motion, a short fade.
 */
const ease = 'expo.inOut';
const reduced = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
const circle = (radius, { x, y }) => `circle(${radius}px at ${x}px ${y}px)`;
const coverRadius = ({ x, y }) => Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
const centre = (el) => (({ left, top, width, height }) => ({ x: left + width / 2, y: top + height / 2 }))(el.getBoundingClientRect());

let pointer = null;
let link = null;
let mode = null;
let morph = null;
let bypass = false;

const curtain = Object.assign(document.createElement('div'), { className: 'kit-curtain' });
curtain.setAttribute('aria-hidden', 'true');

// The panel as an image: a playing video's current frame once it's past its opening, else its
// poster or image.
const snapshot = (media) => {
  const video = media.querySelector('video');

  if (video && video.readyState >= 2 && !video.paused && video.currentTime > 1.5) {
    const canvas = Object.assign(document.createElement('canvas'), { width: video.videoWidth, height: video.videoHeight });
    canvas.getContext('2d').drawImage(video, 0, 0);

    try {
      return canvas.toDataURL('image/jpeg', 0.9);
    } catch {
      // A video the page can't read falls back to its poster.
    }
  }

  return video?.poster || media.querySelector('img')?.currentSrc || '';
};

const leave = () => {
  const media = link?.closest('[data-morph]')?.querySelector('[data-morph-media]');

  if (media && !reduced()) {
    const rect = media.getBoundingClientRect();
    mode = 'morph';
    morph = Object.assign(document.createElement('div'), { className: 'kit-morph' });
    Object.assign(morph.style, { top: `${rect.top}px`, left: `${rect.left}px`, width: `${rect.width}px`, height: `${rect.height}px`, backgroundImage: `url("${snapshot(media)}")` });
    document.documentElement.append(morph);
    media.style.visibility = 'hidden';

    return gsap.to(morph, { top: 0, left: 0, width: innerWidth, height: innerHeight, borderRadius: 0, duration: 1, ease });
  }

  mode = 'curtain';
  const at = pointer ?? (link ? centre(link) : { x: innerWidth / 2, y: innerHeight / 2 });
  gsap.set(curtain, { display: 'block', opacity: 1, clipPath: circle(0, at) });

  return reduced()
    ? gsap.fromTo(curtain, { clipPath: 'none', opacity: 0 }, { opacity: 1, duration: 0.2 })
    : gsap.to(curtain, { clipPath: circle(coverRadius(at), at), duration: 0.8, ease });
};

const frame = () => new Promise((done) => requestAnimationFrame(done));

const enter = async () => {
  if (mode === 'morph') {
    const target = document.querySelector('[data-morph-target] img, img[data-morph-target]');
    await Promise.race([target?.decode?.().catch(() => {}), new Promise((done) => setTimeout(done, 1500))]);
    await frame();
    await gsap.to(morph, { opacity: 0, duration: 0.8, ease: 'power2.inOut' });
    morph.remove();
  } else {
    const anchor = document.querySelector('[data-curtain-anchor]');
    const to = anchor ? centre(anchor) : { x: innerWidth - 60, y: 50 };
    await (reduced() ? gsap.to(curtain, { opacity: 0, duration: 0.2 }) : gsap.to(curtain, { clipPath: circle(0, to), duration: 0.9, ease, delay: 0.1 }));
    gsap.set(curtain, { display: 'none' });
  }

  mode = null;
  pointer = null;
  link = null;
};

export function startTransitions() {
  document.documentElement.append(curtain);

  document.addEventListener('pointerdown', (event) => {
    pointer = { x: event.clientX, y: event.clientY };
    link = event.target.closest?.('a');
  }, { capture: true });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      pointer = null;
      link = document.activeElement?.closest?.('a') ?? null;
    }
  }, { capture: true });

  document.addEventListener('livewire:navigate', (event) => {
    // Our own navigate after the leave animation; back and forward stay Livewire's.
    if (bypass) {
      bypass = false;

      return;
    }

    if (event.detail.history) {
      return;
    }

    // A click while a transition runs is ignored rather than raced.
    event.preventDefault();

    if (mode) {
      return;
    }

    const url = String(event.detail.url);

    Promise.resolve(leave()).then(() => {
      bypass = true;
      window.Livewire.navigate(url);
    });
  });

  document.addEventListener('livewire:navigated', () => {
    if (mode) {
      enter();
    }
  });
}

/**
 * Cards with [data-hover-video] (<x-kit.media-card>) play their video only while the pointer is
 * over them or they have keyboard focus. Nothing downloads until the first hover (preload="none");
 * leaving resets it to its poster. With reduced motion, the poster stays.
 */
const toggle = (playing) => (event) => {
  const card = event.target.closest?.('[data-hover-video]');

  // Moving between the card's own children isn't entering or leaving it.
  if (!card || card.contains(event.relatedTarget) || matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  const video = card.querySelector('video');

  if (playing) {
    video.play().catch(() => {});
  } else {
    video.pause();
    video.load();
  }
};

export function startHoverVideos() {
  document.addEventListener('mouseover', toggle(true));
  document.addEventListener('mouseout', toggle(false));
  document.addEventListener('focusin', toggle(true));
  document.addEventListener('focusout', toggle(false));
}

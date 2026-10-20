/**
 * Videos below the fold (<video data-lazy-src>) show their poster and only download once they
 * come near the viewport, then play.
 */
const observer = 'IntersectionObserver' in window
  ? new IntersectionObserver((entries) => entries.forEach(({ isIntersecting, target }) => {
    if (!isIntersecting) {
      return;
    }

    observer.unobserve(target);
    target.src = target.dataset.lazySrc;
    target.removeAttribute('data-lazy-src');
    target.play().catch(() => {});
  }), { rootMargin: '600px 0px' })
  : null;

export function startLazyVideos() {
  document.querySelectorAll('video[data-lazy-src]').forEach((video) => {
    if (observer) {
      observer.observe(video);
    } else {
      video.src = video.dataset.lazySrc;
    }
  });
}

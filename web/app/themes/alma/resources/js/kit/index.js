import { startReveals } from './reveal';
import { startCarousels } from './carousel';
import { startLazyVideos } from './lazy-video';
import { startHoverVideos } from './hover-video';

/**
 * The front-end kit (docs/design-system.md → "Front-end kit"). Each module sets up what's on the
 * page, again after wire:navigate swaps a page in, and setting up twice is harmless. Page
 * transitions load only when the layout switches them on (PAGE_TRANSITIONS, with WIRE_NAVIGATE).
 */
export function startKit() {
  const setUp = () => {
    startReveals();
    startCarousels();
    startLazyVideos();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setUp);
  } else {
    setUp();
  }

  document.addEventListener('livewire:navigated', setUp);
  startHoverVideos();

  if (document.body.dataset.kitTransitions === 'on') {
    import('./transitions').then(({ startTransitions }) => startTransitions());
  }
}

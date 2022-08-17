import { Inertia } from './inertia/Inertia';
import { Satellite } from './satellite/Satellite';

export const Solar = {
  start: () => {
    document.querySelector('.preloader').classList.add('is-loaded')
    Inertia.init();
    Satellite.init();
  },
}

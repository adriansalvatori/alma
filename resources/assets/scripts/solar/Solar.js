import { Inertia } from './inertia/Inertia';
import { Satellite } from './satellite/Satellite';

export const Solar = {
  start: () => {
    Inertia.init();
    Satellite.init();
  },
}

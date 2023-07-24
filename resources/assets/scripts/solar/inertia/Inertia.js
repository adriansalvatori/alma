import { Jarallax, Barba, Locomotive } from '../utils';
import {useGravity} from "../gravity/Gravity";
import feather from 'feather-icons';

const start = () => {
  window.addEventListener('popstate', function(event){
    const to = event.state.states[event.state.index].url;
    location.assign(to);
  }, false);
  
  Jarallax.setup();
  
  feather.replace({
    class: 'feather',
    'stroke-width': 2,
  });
};

export const Inertia = {
  init: () => {
    window.scroll = Locomotive.setup();
    start();

    Barba.setup();
    window.inertia.hooks.beforeEnter(() => start());
    window.inertia.hooks.after(() => {
      scroll.destroy();
      useGravity();
      scroll.init();
    });
  },
};
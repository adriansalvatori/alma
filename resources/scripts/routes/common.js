//import Alpine from 'alpinejs';
import { Transitions } from '../transitions';

import {
  Solar,
} from '../solar';
import {
  Carousel,
  Store,
  Menu,
} from '../util';

const preloader = document.querySelector('.preloader');

export const Common = {
  init() {
    window.Alpine = Alpine;
    Alpine.start();
    Menu.init();
    Store.setup();
    Solar.start();
    Carousel.init();
    preloader.classList.remove('is-loading');
  },
  finalize() {
    window.useInertia.before(() => {
    })
    window.useInertia.after(() => {
      Carousel.init();
    })
    window.inertia.setup.transitions = Transitions;
    window.inertia.init(window.inertia.setup);
  },
};

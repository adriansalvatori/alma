import barba from '@barba/core';

export const Barba = {
  setup: () => {

    window.inertia = barba;
    window.useInertia = window.inertia.hooks;

    // before everything. This is pretty-much Document BEFORE ready.
    barba.hooks.before(() => {
      barba.wrapper.classList.add('is-animating');
    });

    // Enter 
    barba.hooks.beforeEnter(() => {

    });

    barba.hooks.enter(() => {

    });

    barba.hooks.afterEnter(() => {

    });

    // Leave

    barba.hooks.beforeLeave(() => {

    });

    barba.hooks.leave(() => {

    })

    barba.hooks.afterLeave(() => {

    });

    // After Everything
    barba.hooks.after(() => {
      barba.wrapper.classList.remove('is-animating');
    });

    // Setting up propperties
    barba.setup = {
      debug: false,
      timeout: 15000,
      views: ['home'],
      schema: {
        prefix: 'data-solar',
      }
    };
  },
};

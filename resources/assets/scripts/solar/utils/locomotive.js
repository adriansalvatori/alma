import LoconativeScroll from 'loconative-scroll'; // Changed Locomotive for Loconative

export const Locomotive = {
  setup: () => {
    const scroll = new LoconativeScroll({ // Changed Locomotive for Loconative
      el: document.querySelector('[data-inertia-container]'),
      name: 'inertia',
      smooth: true,
      class: 'is-revealed',
    });

    return scroll;
  },
};

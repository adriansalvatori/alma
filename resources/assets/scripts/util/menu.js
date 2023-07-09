export const Menu = {
  init() {
    const menu = document.querySelector('#full_menu');
    const trigger = document.querySelector('#open_menu');

    if (menu && trigger) {
      const menu_action = () => {
        menu.classList.toggle('is-disabled');
        menu.querySelectorAll('[data-inertia-reveal]').forEach(element => {
          element.classList.toggle('is-revealed');
        });
      }

      trigger.onclick = () => {
        menu_action()
      }

      menu.querySelectorAll('a').forEach(element => {
        element.onclick = () => {
          menu_action()
        }
      })

      if (!menu.classList.contains('is-disabled')) document.querySelectorAll('a').forEach(element => {
        element.onclick = () => {
          menu_action()
        }
      })
    }
  },
};

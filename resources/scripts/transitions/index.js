//import { gsap } from "gsap";
const loaderElements = [
    document.querySelector('.preloader'),
    document.querySelector('.layout')
]


export const Transitions = [{
  name: 'default',

  leave() {
    return new Promise((done) => {
        loaderElements.forEach(el => el.classList.add('is-loading'));
        setTimeout(() => {
			done();
		}, 600);
    });
  },
   enter() {
    // animate loading screen away
    loaderElements.forEach(el => el.classList.remove('is-loading'));
  }
}]

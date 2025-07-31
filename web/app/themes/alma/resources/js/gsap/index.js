import { Carousel } from "./Carousel";
import { Reveal } from "./Reveal";

export const GsapAnimations = {
  init() {
    document.addEventListener("DOMContentLoaded", () => {
      Carousel.init();
      Reveal.init();
      console.log("gsap animations started");
    })
  }
};

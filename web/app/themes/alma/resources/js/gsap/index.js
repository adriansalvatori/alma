import { Carousel } from "./Carousel";
import { Reveal } from "./Reveal";
import { Parallax } from "./Parallax";

export const GsapAnimations = {
  init() {
    document.addEventListener("DOMContentLoaded", () => {
      Carousel.init();
      Reveal.init();
      Parallax.init();
      console.log("gsap animations started");
    })
  }
};

import {
  jarallax,
  jarallaxVideo,
} from "jarallax";

export const Jarallax = {
  setup() {
    jarallaxVideo();
    const parallaxOptions = {
      imgSize: 'cover', 
      imgPosition: '25% 50%',
    };

    jarallax(document.querySelectorAll('.is-parallax-contain'), {
      ...parallaxOptions,
      speed: 0.9,
    });
    jarallax(document.querySelectorAll('.is-parallax-cover'), {
      ...parallaxOptions,
      speed: 0.4,
    });

    document.querySelectorAll('.is-parallax-video').forEach(element => {
      jarallax(element, {
        speed: 0.8,
        videoSrc: `mp4:${element.dataset.url}`,
      });
    });
  },
};

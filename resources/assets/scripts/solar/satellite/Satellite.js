import MouseFollower from "mouse-follower";
import gsap from "gsap";
import Gravity from "../gravity/Gravity";

/**
 * @docs https://github.com/Cuberto/mouse-follower
 */

const config = {
  el: null,
  container: document.body,
  className: 'satellite-cursor',
  innerClassName: 'satellite-cursor-inner',
  textClassName: 'satellite-cursor-text',
  mediaClassName: 'satellite-cursor-media',
  mediaBoxClassName: 'satellite-cursor-media-box',
  iconSvgClassName: 'satellite-svgsprite',
  iconSvgNamePrefix: '-',
  iconSvgSrc: '',
  dataAttr: 'cursor',
  hiddenState: '-hidden',
  textState: '-text',
  iconState: '-icon',
  activeState: '-active',
  mediaState: '-media',
  stateDetection: {
    '-pointer': 'a,button',
    '-hidden': 'iframe',
  },
  visible: true,
  visibleOnState: false,
  speed: 0.55,
  ease: 'expo.out',
  overwrite: true,
  skewing: 0,
  skewingText: 2,
  skewingIcon: 2,
  skewingMedia: 2,
  skewingDelta: 0.001,
  skewingDeltaMax: 0.15,
  stickDelta: 0.15,
  showTimeout: 20,
  hideOnLeave: true,
  hideTimeout: 300,
  hideMediaTimeout: 300,
}

export const Satellite = {
  init: () => {
    MouseFollower.registerGSAP(gsap);
    new MouseFollower(config);

    document.querySelectorAll('[data-gravity]')
      .forEach(element => new Gravity(element, {
        y: 0.3, // horizontal
        x: 0.3, // vertical
        s: 0.2, // speed
        rs: 0.7, // release speed
      }));

    console.log('Satellite Initiated');
  },
}

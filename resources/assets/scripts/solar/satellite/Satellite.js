import MouseFollower from "mouse-follower";
import gsap from "gsap";

/**
 * @docs https://github.com/Cuberto/mouse-follower
 */

export const Satellite = {
  init: () => {
    MouseFollower.registerGSAP(gsap);
    const cursor = new MouseFollower({
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
        '-hidden': 'iframe'
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
      hideMediaTimeout: 300
    });

    console.log('Satellite Initiated');
  }
}

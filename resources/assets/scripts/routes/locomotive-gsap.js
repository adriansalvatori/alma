import LocomotiveScroll from 'locomotive-scroll';
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

export const locomo = ()=>{

    gsap.registerPlugin(ScrollTrigger);
    const locoScroll = new LocomotiveScroll({
    el: document.querySelector(".scrollContainer"),
        smooth: true,
        multiplier: 0.8
    }); 
    
    // Esto de aqui cada vez que hagamos scroll actualiza el ScrollTrigger
    locoScroll.on("scroll", ScrollTrigger.update);
    
    // Esto es el scroll virtual que nos ayuda a ejecutar animaciones que necesiten el scroll
    ScrollTrigger.scrollerProxy(".scrollContainer", {
        scrollTop(value) {
            return arguments.length ? locoScroll.scrollTo(value, 0, 0) : locoScroll.scroll.instance.scroll.y;
        },
        // Aqui definimos el Left o el top dependiendo de donde inicia el scroll en Vertical o Horizontal
        getBoundingClientRect() {
        return {top: 0, left: 0, width: window.innerWidth, height: window.innerHeight};
        },
        // Esto va verifycando lo que hace locomotive para saber en que puinto esta el Scroll o algo asi
        pinType: document.querySelector(".scrollContainer").style.transform ? "transform" : "fixed"
    });
    
    // Esto Ejecuta todas las animaciones de Aos
    let allAos = document.querySelectorAll('.aos-init');

    allAos.forEach(el =>{
        ScrollTrigger.create({
            trigger: el,
            start: el.dataset.start || "top 100%",
            toggleClass: {
                targets: el, 
                className: "aos-animate"
            },
            end: el.dataset.end || "bottom",
            scroller:".scrollContainer",
        })
    })

    let toggleZone = document.querySelectorAll('.toggle-trigger');

    toggleZone.forEach(el =>{
        let targets = document.querySelectorAll(el.dataset.toggleTarget);
        ScrollTrigger.create({
            trigger: el,
            start: el.dataset.toggleStart || "top 0",
            onToggle: ()=>{
                (targets.length > 0 ? targets : [el]).forEach(target =>{
                    target.classList.toggle( el.dataset.toggleClass || 'is-inview')
                })
            },
            end: el.dataset.toggleEnd || "bottom 0",
            scroller:".scrollContainer",
        })
    })
    

    // Esto actualiza el ScrollTrigger y Locomotive para evitar problemas con los paddings de animaciones y esas cosas
    ScrollTrigger.addEventListener("refresh", () => locoScroll.update());
    ScrollTrigger.refresh();
}
import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/themes/splide-default.min.css';

export const slider = {
    type1 : (props, structure)=>{
        let slider = document.querySelector(props.element);
        structure = !structure;
        if(slider) {
            if(!structure){
                let childs = slider.children;
                let items = [];
                Array.from(childs).forEach(element => {
                    items.push(`
                        <li class="splide__slide column is-flex">
                            ${element.outerHTML}
                        </li>
                    `)
                });
                let output = `
                <div class="splide__track is-full-width">
                    <ul class="splide__list is-flex">
                        ${items.join("")}
                    </ul>
                </div>
                <div class="align-items-center is-flex justify-space-between splide__arrows">
                    <div class="splide__arrow--prev is-flex align-items-center has-cursor-pointer">
                        <i class="is-relative has-margin-right-5" data-feather="arrow-right"></i>
                        <span>Prev</span>
                    </div>
                    <div class="splide__arrow--next is-flex align-items-center has-cursor-pointer">
                        <span>Next</span>
                        <i class="is-relative has-margin-left-5" data-feather="arrow-right"></i>
                    </div>
                </div>`
                slider.innerHTML = output;
            }

            new Splide( props.element, props.props ).mount();
        }
    },
}
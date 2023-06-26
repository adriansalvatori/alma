import { Inertia } from './inertia/Inertia';
import { Satellite } from './satellite/Satellite';

export const Solar = {
  start: () => {
    document.querySelector('.preloader').classList.add('is-loaded')
    Inertia.init();
    Satellite.init();
    
    //codigo para volver atras en la pagina
    window.addEventListener('popstate', function(event){
      const to = event.state.states[event.state.index].url;
      location.assign(to);
    }, false);
  },
}

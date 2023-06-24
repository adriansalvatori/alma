import {Solar} from '../solar';

export default {
  init() {
    //codigo para volver atras en la pagina
    window.addEventListener('popstate', function(event){
      const to = event.state.states[event.state.index].url;
      location.assign(to);
    }, false);
  },
  finalize() {
    Solar.start();
  },
};

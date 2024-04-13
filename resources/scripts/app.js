import domReady from '@roots/sage/client/dom-ready';
import '../styles/app.scss';
import {Common} from './routes/common';


/**
 * Application entrypoint
 */
domReady(async () => {
  Common.init();
  Common.finalize();
});

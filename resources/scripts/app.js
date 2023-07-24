import domReady from '@roots/sage/client/dom-ready';

// import local dependencies
import Router from './util/Router';
import common from './routes/common';
import home from './routes/home';
import aboutUs from './routes/about';
import '../styles/app.scss';


/** Populate Router instance with DOM routes */
const routes = new Router({
  // All pages
  common,
  // Home page
  home,
  // About Us page, note the change from about-us to aboutUs.
  aboutUs,
});

/**
 * Application entrypoint
 */
domReady(async () => {
  // ...
  routes.loadEvents();
});

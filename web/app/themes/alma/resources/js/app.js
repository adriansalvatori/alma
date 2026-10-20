// Fonts and images referenced from CSS go through Vite (hashed). Page art in
// resources/images/pages is handled separately by vite/theme-images.js.
import.meta.glob(['../images/*.{png,jpg,jpeg,svg,webp,avif}', '../fonts/**']);

// The front-end kit: reveals, carousels, lazy and hover videos, and (opt-in) page transitions.
import { startKit } from './kit';

startKit();

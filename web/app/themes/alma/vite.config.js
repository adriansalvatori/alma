import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { wordpressPlugin } from '@roots/vite-plugin';
import themeImages from './vite/theme-images.js';

// laravel-vite-plugin wants an APP_URL; the dev server only uses it for its own links.
if (! process.env.APP_URL) {
  process.env.APP_URL = 'http://example.test';
}

export default defineConfig({
  base: '/app/themes/alma/public/build/',
  plugins: [
    tailwindcss(),
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/editor.css',
        'resources/js/editor.js',
      ],
      refresh: true,
    }),

    // Maps @wordpress/* imports to the wp.* globals and writes editor.deps.json, which
    // app/setup.php enqueues before the editor script.
    wordpressPlugin(),

    // resources/images/pages/** → public/images/** with filenames kept and .webp siblings.
    themeImages(),
  ],
  build: {
    // Dynamic imports are fetched when used, not preloaded on every page.
    modulePreload: false,
  },
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    },
  },
});

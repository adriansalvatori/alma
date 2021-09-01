const mix = require('laravel-mix');
require('@tinypixelco/laravel-mix-wp-blocks');
require('dotenv').config({ path: '../../../../.env' });
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Sage application. By default, we are compiling the Sass file
 | for your application, as well as bundling up your JS files.
 |
 */

mix
  .setPublicPath('./public')
  .browserSync(process.env.WP_HOME);

mix
  .sass('resources/assets/styles/app.scss', 'styles')
  .sass('resources/assets/styles/editor.scss', 'styles')
  .options({
      processCssUrls: false,
      enabled:false,
      whitelist: require('purgecss-with-wordpress').whitelist,
      whitelistPatterns: require('purgecss-with-wordpress').whitelistPatterns,
  });

mix
  .js('resources/assets/scripts/app.js', 'scripts')
  .js('resources/assets/scripts/customizer.js', 'scripts')
  .blocks('resources/assets/scripts/editor.js', 'scripts')
  .autoload({ jquery: ['$', 'window.jQuery'] })
  .extract();

mix
  .copyDirectory('resources/assets/images', 'public/images')
  .copyDirectory('resources/assets/fonts', 'public/fonts');

mix
  .sourceMaps()
  .version();
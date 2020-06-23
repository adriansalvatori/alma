# [Alma.salvatori.co](https://alma.salvatori.co)

[![Packagist](https://img.shields.io/packagist/vpre/roots/sage.svg?style=flat-square)](https://packagist.org/packages/salvatori/alma)


Alma is a WordPress starter theme with a modern development workflow.

**Alma  is in active development and is currently in alpha. The `master` branch tracks Alma  development. If you want a stable version.**

## Features

* Sass for stylesheets
* Modern JavaScript
* [Laravel Mix](https://github.com/JeffreyWay/laravel-mix) for compiling assets and concatenating and minifying files
* [Browsersync](http://www.browsersync.io/) for synchronized browser testing
* [Blade](https://laravel.com/docs/5.8/blade) as a templating engine
* [Bulma](https://bulma.io/)

See a working example at [example-alma.salvatori.co](https://example-alma.salvatori.co/).

## Requirements

Make sure all dependencies have been installed before moving on:

* [WordPress](https://wordpress.org/) >= 5.4
* [PHP](https://secure.php.net/manual/en/install.php) >= 7.2.0 (with [`php-mbstring`](https://secure.php.net/manual/en/book.mbstring.php) enabled)
* [Composer](https://getcomposer.org/download/)
* [Node.js](http://nodejs.org/) >= 8.0.0
* [Yarn](https://yarnpkg.com/en/docs/install)

## Theme installation

Install Alma using Composer from your WordPress themes directory (replace `your-theme-name` below with the name of your theme):

```sh
# @ app/themes/ or wp-content/themes/
$ composer create-project salvatori/alma new-theme-name
```

To install the latest development version of Alma, add `dev-master` to the end of the command:

```shell
$ composer create-project salvatori/alma new-theme-name dev-master
```

During theme installation you will have options to update `style.css` theme headers, select a CSS framework, and configure Browsersync.

## Theme structure

```sh
themes/your-theme-name/   # → Root of your Alma based theme
├── app/                  # → Theme PHP
│   ├── Composers/        # → View composers
│   ├── Providers/        # → Service providers
│   ├── admin.php         # → Theme customizer setup
│   ├── filters.php       # → Theme filters
│   ├── helpers.php       # → Helper functions
│   └── setup.php         # → Theme setup
├── config/               # → Config files
│   ├── app.php           # → Application configuration
│   ├── assets.php        # → Asset configuration
│   ├── filesystems.php   # → Filesystems configuration
│   └── view.php          # → View configuration
├── composer.json         # → Autoloading for `app/` files
├── composer.lock         # → Composer lock file (never edit)
├── dist/                 # → Built theme assets (never edit)
├── functions.php         # → Composer autoloader, Acorn bootloader
├── index.php             # → Never manually edit
├── node_modules/         # → Node.js packages (never edit)
├── package.json          # → Node.js dependencies and scripts
├── resources/            # → Theme assets and templates
│   ├── assets/           # → Front-end assets
│   │   ├── fonts/        # → Theme fonts
│   │   ├── images/       # → Theme images
│   │   ├── scripts/      # → Theme JS
│   │   └── styles/       # → Theme stylesheets
│   └── views/            # → Theme templates
│       ├── components/   # → Component templates
│       ├── layouts/      # → Base templates
│       └── partials/     # → Partial templates
├── screenshot.png        # → Theme screenshot for WP admin
├── storage/              # → Storage location for cache (never edit)
├── style.css             # → Theme meta information
├── vendor/               # → Composer packages (never edit)
└── webpack.mix.js        # → Laravel Mix configuration
```

## Theme setup

Edit `app/setup.php` to enable or disable theme features, setup navigation menus, post thumbnail sizes, and sidebars.

## Theme development

* Run `yarn` from the theme directory to install dependencies
* Update `webpack.mix.js` with your local dev URL

### Build commands

* `yarn start` — Compile assets when file changes are made, start Browsersync session
* `yarn build` — Compile and optimize the files in your assets directory
* `yarn build:production` — Compile assets for production

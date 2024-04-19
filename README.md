
# ⚡️ Alma Lightning (5.0.0) Wordpress Theme. (yeah, right, it's just a wordpress theme 😂)

- Based on [Sage](https://roots.io/sage/) version [10](https://github.com/roots/sage/)
- Using [Vite](https://vitejs.dev)

## Features
- Solar (Sugary implementation of: Locomotive with Lenis, Gsap, Barba, Mouse Follower, Bulma, and some other nice stuff)
- Laravel Routing
- Laravel Blade
- Laravel Components, Composers and Controllers
- Sage Directives
- Sage Wooocommerce
- Alpine

## Tech

- [Acorn](https://roots.io/acorn/docs/installation/) v4
- [PHP](https://secure.php.net/manual/en/install.php) >= 8.2 (
  with [`php-mbstring`](https://secure.php.net/manual/en/book.mbstring.php) enabled)
- [Composer](https://getcomposer.org/download/)
- [Vite](https://vitejs.dev) >= 3.1.0
- [Node.js](http://nodejs.org/) >= 20.0.0
- [Yarn](https://yarnpkg.com/en/docs/install)

## Theme installation

- **This framework is shipped with Acorn v3. Read the docs, please (https://roots.io/acorn/docs/)**
- Install Alma using Composer from your WordPress themes directory (replace `your-theme-name` below with the name of
  your theme):

```shell
# @ app/themes/ or wp-content/themes/
$ composer create-project salvatori/alma your-theme-name
```

To install the latest development version of Alma, add `dev-lightning` to the end of the command:

```shell
$ composer create-project salvatori/alma your-theme-name dev-lightning
```

## Theme structure

```sh
themes/alma/              # → Root of your Alma based theme
├── app/                  # → Theme PHP
│   ├── Providers/        # → Service providers
│   ├── View/             # → View models
│   ├── filters.php       # → Theme filters
│   ├── helpers.php       # → Global helpers
│   ├── medias.php        # → Medias helper
│   └── setup.php         # → Theme setup
├── composer.json         # → Autoloading for `app/` files
├── routes/web.php        # → Place non WP routes here.
├── public/               # → Built theme assets (never edit)
├── functions.php         # → Theme bootloader
├── index.php             # → Theme template wrapper
├── node_modules/         # → Node.js packages (never edit)
├── package.json          # → Node.js dependencies and scripts
├── resources/            # → Theme assets and templates
│   ├── fonts/            # → Theme fonts
│   ├── images/           # → Theme images
│   ├── scripts/          # → Theme javascript
│   ├── styles/           # → Theme stylesheets
│   └── views/            # → Theme templates
│       ├── components/   # → Component templates
│       ├── forms/        # → Form templates
│       ├── layouts/      # → Base templates
│       ├── partials/     # → Partial templates
        └── woocommerce/  # → Woocommerce templates
├── screenshot.png        # → Theme screenshot for WP admin
├── style.css             # → Theme meta information
├── vendor/               # → Composer packages (never edit)
└── vite.config.js        # → Vite configuration
```

## Theme development

- Run `yarn` from the theme directory to install dependencies
- Update `vite.config.js` for bundler fine tuning

### Build commands

- `yarn dev` — Start dev server and hot module replacement
- `yarn build` — Compile assets
- `yarn lint` — Lint stylesheets & javascripts
- `yarn lint:css` — Lint stylesheets
- `yarn lint:js` — Lint javascripts

### Hot Module Replacement

#### Project Side

Add the following variables in your project `.env`

```sh
# Hot module reload enabled? This should be turned off in production.
HMR_ENABLED=true
# Endpoint where the bundler serve your assets
HMR_ENTRYPOINT=http://localhost:5173
# Add an APP_KEY for LiveWire
APP_KEY= #some 32 characters randomized string
```

#### Maintainance Mode

Alma comes with a basic and simple maintainance mode implemented. If you want to enable it, just use this .env variable: 

```
WP_MAINTAINANCE_MODE="true"
```

## Documentation

- [Sage documentation](https://roots.io/sage/docs/)
- [Controller documentation](https://github.com/soberwp/controller#usage)
- [Vite](https://vitejs.dev/guide/)

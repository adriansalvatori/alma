# Alma Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and releases are tagged `v-YYYYMMDD-vN` (one tag per production deploy; see
web/app/themes/alma/docs/deployment.md).

## [8.1.0] - 2026-10-20

### Added
- The front-end kit, Movidagráfica's design system made reusable and opt-in
  (`docs/design-system.md` → "Front-end kit"):
  - `kit.css`: an editorial type scale (`type-hero` to `type-body`) on variables each project
    overrides, including `--type-display-case` and `--type-display-weight`.
  - `<x-kit.*>` components: pill, arrow-link, marquee, drag carousel (+ item), full-screen menu,
    media card (video on hover, morph source) and a numbered document page.
  - `resources/js/kit`: scroll reveals, the physics carousel, lazy and hover videos, idempotent and
    re-run after `wire:navigate`.
  - Page transitions on `wire:navigate` (`PAGE_TRANSITIONS`, off by default): a media card's panel
    grows into the next page; any other link draws the `--kit-curtain` circle.
- `gsap` as a theme dependency; rendering tests for every kit component.

## [8.0.0] - 2026-10-19

Alma v8: a rebuild around the architecture proven on Remote Leverage v2, without the AWS/Docker
infrastructure, so every project can deploy to SSH-capable shared hosting.

### Added
- PHP 8.4, WordPress 7.1, Acorn 6, Livewire 4.4, Flux 2, ACF Pro (Composer) and The SEO Framework.
- A module system: each business area is a folder under `app/Domains` with its own provider,
  listed in `config/alma.php` and switchable per environment with `ALMA_DISABLED_MODULES`.
- `wp acorn alma:deploy`: MySQL-locked, idempotent post-deploy tasks (migrations, module tasks,
  rewrite flush, view cache).
- After-response work with no queue worker (`Deferred`), with an optional cron-drained database queue.
- A Pest suite that boots the real Acorn application on SQLite with a fake WordPress layer, with no
  network access and every secret blanked; Larastan level 5; one Pint preset everywhere.
- GitHub Actions: CI on every pull request, plus a tag-gated production deploy and a branch-gated
  staging deploy over SSH/rsync, with atomic `current` symlink switches, release pruning and a
  post-deploy health check.
- `bin/` scripts: `setup.sh` (first-time install), `deploy`, `rollback`, `pull` (remote database and
  uploads to local, URL-rewritten), `backup`.
- `.deployignore`, `.env.example` with every variable documented, `auth.json.example`, `.mcp.json`
  for the WordPress MCP adapter, and `wp-cli.local.yml.example` with SSH aliases.

### Changed
- The theme's documentation lives in `web/app/themes/alma/docs/`. The root guides (API,
  ARCHITECTURE, CONTRIBUTING, DEPLOYMENT, DEVELOPER_GUIDE, QUICKSTART, TESTING) were removed:
  they described features that did not exist.

### Removed
- The `deploy:zip` script, which packaged `.env` and the uploads directory into the archive.
- Substrate (requires Acorn 5), the Wompi and WooCommerce plugins from the base install
  (WooCommerce is an optional module: `bin/setup.sh --with-woocommerce`).

## [Unreleased] (pre-v8)

### Added
- Comprehensive testing suite with Pest PHP
- Architecture documentation
- Developer guide
- Testing guide
- Deployment guide
- Enhanced service architecture
- Custom WP-CLI commands for block generation
- Native single-file Gutenberg blocks system
- Livewire 4 single-file components
- Flux UI integration
- WooCommerce template overrides
- Authentication system with 2FA support
- Role management service
- Dashboard widget system
- View composers for shared data
- Middleware for auth/2FA
- Route system with Laravel-style controllers
- Vite build system with custom blade blocks plugin
- Tailwind CSS 4 integration
- Dark mode support
- Editor styles for block editor

### Changed
- Modernized project structure
- Improved developer experience
- Enhanced documentation
- Better error handling
- Optimized build process

### Fixed
- Block template conflicts with WooCommerce
- FSE template issues
- Script concatenation problems
- Style conflicts with block editor

## [1.0.0] - 2025-03-01

### Initial Release
- Bedrock WordPress boilerplate
- Sage theme with Blade templates
- Acorn integration
- Livewire components
- Flux UI components
- Native Gutenberg blocks
- WooCommerce integration
- Authentication system
- Service architecture
- Modern front-end tooling

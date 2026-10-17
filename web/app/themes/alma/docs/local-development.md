# Local development

## Prerequisites

| Tool | Version | Notes |
| :--- | :--- | :--- |
| PHP | 8.4+ | with `pdo_mysql`, `pdo_sqlite`, `mbstring`, `intl`, `gd`, `zip` |
| Composer | 2.x | |
| Node | 20.19+ or 22.12+ | for Vite; never needed on the server |
| WP-CLI | 2.x | `wp acorn …` runs Acorn's Artisan commands |
| MySQL / MariaDB | 8.x / 10.6+ | DBngin, Herd Pro, Homebrew, Docker: anything listening on a port |
| A local web server | | Herd or Valet (serve the repository's `web/` directory), or Apache/nginx with the document root at `web/` |
| `mysql` + `mysqldump` clients | | only for `bin/pull` and `bin/backup` (WP-CLI's `db import`/`export` use them). DBngin ships them in `/Users/Shared/DBngin/mysql/<version>/bin`; add that to your PATH. |

ACF Pro is licensed. Put your key in `auth.json` (copy `auth.json.example`) before installing, or
Composer cannot download it. Until you do, everything else installs and the site runs without the
ACF blocks.

## First run

```bash
bin/setup.sh
```

The script is safe to re-run. It:

1. checks the prerequisites;
2. writes `.env` from `.env.example` (database, `WP_HOME`), quoting values safely;
3. generates salts and `APP_KEY`;
4. installs Composer dependencies (root and theme), runs `npm install` and builds the assets;
5. creates the database and installs WordPress with a random admin password, **printed once**;
6. activates the theme and plugins and sets the permalinks;
7. creates the Home page from the `alma/home` pattern;
8. runs `wp acorn alma:deploy`.

`bin/setup.sh --with-woocommerce` also adds WooCommerce (the optional Commerce module);
`--yes` accepts every default.

With Herd or Valet, link the project so `http://<folder>.test` serves `web/`:

```bash
cd web && valet link <folder>        # or: herd link <folder>
```

## Daily commands

From the repository root unless noted:

| Command | What it does |
| :--- | :--- |
| `npm run dev` | Vite with hot reload (theme assets, Tailwind) |
| `npm run build` | Production assets in `web/app/themes/alma/public/build` (needed after adding Tailwind classes, if `dev` is not running) |
| `composer check` | Everything CI runs: Pint (root + theme), Larastan, Pest |
| `composer theme test` | Just the Pest suite (`composer theme <script>` runs any theme script) |
| `composer theme lint:fix` | Fix formatting |
| `wp acorn alma:deploy` | Migrations, module deploy tasks, rewrite flush, view cache (what every deploy runs) |
| `wp acorn migrate` | Migrations only |
| `wp acorn list` | Every Acorn command, including the modules' own |
| `bin/pull staging` | Copy a remote database and uploads to this machine ([deployment.md](deployment.md#pulling-production-data-to-your-machine)) |
| `bin/backup` | Snapshot the local database and uploads into `backups/` |

From the theme directory (`web/app/themes/alma`) the same checks are `vendor/bin/pest`,
`vendor/bin/pint --test` and `composer analyse`, exactly as CI runs them.

## What the test suite proves, and what it does not

The Pest suite (`web/app/themes/alma/tests`) creates the **real application**
(`bootstrap/application.php`) for every test, on an in-memory SQLite database with the real
migrations, and replaces WordPress with a small in-memory fake (`tests/Support/WordPress`). The
network is closed (`Http::preventStrayRequests()`) and every secret is blanked in `phpunit.xml`, so
the suite is safe to run anywhere, needs no database server and takes seconds.

It proves:

- routing, controllers, middleware, validation, Livewire components (`Livewire::test()`), Blade
  rendering, events and deferred jobs;
- the module wiring: providers, listeners, deploy tasks, migrations (on SQLite);
- the conventions enforced by tests that scan the codebase.

It does **not** prove:

- behaviour that depends on real WordPress internals (the fake implements what the theme calls,
  no more), or on MySQL-specific SQL;
- that plugins (ACF, The SEO Framework, WooCommerce) behave as the fakes assume;
- anything visual: layout, responsiveness, the block editor.

So: run the suite constantly, and still open the site (and wp-admin) before shipping. After
pulling production data, `wp acorn alma:deploy` plus a click through the main pages is the
cheapest real-WordPress check there is.

## Working with Claude Code

`.mcp.json` connects Claude Code to WordPress through the MCP adapter plugin, using an Application
Password. Export the credentials in your shell; they are never committed:

```bash
export LOCAL_MCP_URL=http://alma.test
export LOCAL_MCP_USER=admin
export LOCAL_MCP_APP_PASSWORD='xxxx xxxx xxxx xxxx xxxx xxxx'   # Users → Profile → Application Passwords
```

WordPress only offers Application Passwords over HTTPS or when the environment type is `local`.
On a plain-HTTP `.test` site, either serve it over HTTPS (`herd secure` / `valet secure`) or add
`WP_ENVIRONMENT_TYPE='local'` to your `.env`.

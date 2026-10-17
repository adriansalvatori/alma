# Architecture

How alma boots, how a request flows, and how a module is put together. Decisions and their reasons are in [adr/](adr/).

## The stack

| Layer | What | Notes |
| :--- | :--- | :--- |
| Project | Roots Bedrock | `.env` config, Composer-managed WordPress in `web/wp`, plugins in `web/app/plugins` |
| WordPress | 7.1 | The Abilities API (6.9+) backs the AI module |
| Theme | `web/app/themes/alma` | Blade templates, an Acorn (Laravel 13 components) container inside WordPress |
| Reactive UI | Livewire 4 + Flux | Single-file components in `resources/views/components/<module>/` |
| Content | ACF Pro + ACF Composer | Code-first blocks in `app/Blocks`, pages as patterns in `patterns/` |
| SEO | The SEO Framework | Settings applied from `config/seo.php` on deploy |
| Build | Vite 8 + Tailwind 4 | Assets are built in CI and shipped; no Node on the server |
| Tests | Pest 5 | A real Acorn app on in-memory SQLite, a fake WordPress layer, network closed |

The hosting target is plain shared hosting with SSH: PHP 8.4 (FPM or LiteSpeed), MySQL, a cron entry. No Redis, no queue worker, no Docker, no Node at runtime.

## Boot

```
web/wp-config.php → config/application.php (.env, constants)
  → WordPress loads plugins, then the theme
    → functions.php
        require vendor/autoload.php
        (require bootstrap/application.php)->boot()      ← one builder, also used by the tests
        app/setup.php, app/filters.php                   ← theme supports, menus, assets only
```

`bootstrap/application.php` returns Acorn's application builder: the theme as base path, `ThemeServiceProvider`, and the `routes/web.php` / `routes/api.php` files. It makes no WordPress calls, so `tests/TestCase.php` creates the exact same application without WordPress.

`ThemeServiceProvider` registers `CoreServiceProvider` (infrastructure every module needs), then each module in `config/alma.php` whose key isn't in `ALMA_DISABLED_MODULES` and whose `enabled()` says its prerequisites exist:

| Key | Module | Enabled when |
| :--- | :--- | :--- |
| `content` | Blocks, patterns-as-pages, editor, assets | always |
| `seo` | robots, noindex, 404s, redirects, TSF settings | always |
| `lead` | Lead capture, attribution, notifications, webhook, wp-admin | always |
| `account` | Login, 2FA, registration, account area | always |
| `commerce` | WooCommerce integration | WooCommerce is active |
| `payment` | Gateway-agnostic payments | `PAYMENTS_ENABLED=true` |
| `ai` | MCP abilities for Claude, AI Access screen | the Abilities API exists and `AI_ENABLED` is not false |

## Two request paths

Acorn runs inside WordPress, so there are two ways a request is answered, and they behave differently:

1. **Acorn routes** (`routes/*.php`, each module's `routes/` folder, Livewire's endpoints). Acorn matches them on `parse_request`, *before* WordPress resolves a page, runs Laravel middleware, and exits. A route wins over a page with the same slug.
2. **Everything else** (pages, posts, archives, wp-admin, wp-login.php, wp-json). WordPress renders it through the template hierarchy into Blade views. **Laravel middleware never runs here.** Behaviour that must apply to every page (redirects, the HTTPS redirect, real 404s, noindex) is a WordPress hook class, not middleware.

Acorn also only calls `$app->terminate()` on path 1. `ThemeServiceProvider::ensureApplicationTerminates()` adds it to WordPress' `shutdown` hook (after `fastcgi_finish_request()`), so work deferred until after the response runs on path 2 too. Without it, deferred jobs are dropped silently on every WordPress-rendered page.

## Modules

A module is a folder under `app/Domains/<Name>` with a provider extending `App\Infrastructure\Providers\ModuleServiceProvider`. The provider *declares* what the module contributes, so its whole wiring is readable at the top of one file:

```php
class LeadServiceProvider extends ModuleServiceProvider
{
    protected array $hooks = [AttributionCookie::class, LeadsAdmin::class];       // register() adds WP hooks
    protected array $listen = [LeadCaptured::class => [RecordLeadDispatch::class]];   // in the request
    protected array $listenAfterResponse = [                                         // after the response
        LeadCaptured::class => [SendLeadNotification::class, SendLeadWebhook::class],
    ];
    protected array $commands = [PurgeLeadsCommand::class];                           // wp acorn …
    protected array $deployTasks = [GrantLeadCapability::class];                      // alma:deploy steps
    protected ?string $migrations = __DIR__.'/Database/Migrations';
    // $webRoutes / $apiRoutes: route files owned by the module
}
```

Inside a module, by convention:

| Folder | Holds |
| :--- | :--- |
| `Actions/` | One unit of work per class, one public `execute()` |
| `Data/` | `readonly` DTOs (`fromArray()` / `toArray()`) |
| `Events/`, `Listeners/` | Past-tense facts; one listener per destination |
| `Models/` | Eloquent models. Table names are unprefixed (`alma_leads`); Acorn adds `$wpdb->prefix` |
| `Services/`, `Support/` | Policy, settings, helpers |
| `Hooks/`, `Admin/` | WordPress hook registrars and wp-admin screens (`register()` only adds hooks) |
| `Http/`, `routes/` | Controllers, middleware, the module's route files |
| `Database/Migrations/` | Anonymous-class migrations |

Deleting a module means deleting its folder and its line in `config/alma.php` (plus any views it owns under `resources/views/components/<module>/`).

## Work after the response, without a worker

Anything that calls a third party (CRM webhook, email, analytics) goes through `$listenAfterResponse`, which wraps the listener in `App\Infrastructure\Queue\Deferred`:

- `QUEUE_CONNECTION=sync` (the default): the job runs after the response has been sent. The visitor never waits on a slow API, and no worker is needed.
- `QUEUE_CONNECTION=database`: the same job goes onto the `jobs` table (it always exists), drained by a cron running `wp acorn queue:work --stop-when-empty --max-time=50`.

Listeners that only touch the database and must finish before the response go in `$listen`.

## Deploys

`wp acorn alma:deploy` is the single post-deploy command. It takes a MySQL named lock, then runs every tagged `DeployTask` in priority order:

| Priority | Task | Fatal |
| :--- | :--- | :--- |
| 0 | Run migrations | yes |
| 10–80 | Module tasks (capabilities, TSF settings, agent reconciliation, …) | no |
| 90 | Flush rewrite rules | no |
| 100 | Clear caches and precompile Blade views | no |

Tasks are idempotent; running the command twice equals running it once. The deploy script runs it in the new release *before* switching `current`, so a failed migration never takes the site down. See [deployment.md](deployment.md).

## Tests

`vendor/bin/pest` creates the real application from `bootstrap/application.php` for each test, on in-memory SQLite, with:

- a fake WordPress layer (`tests/Support/WordPress`): the functions the theme calls, backed by an in-memory store reset before every test; module-specific fakes live in their own files;
- `Http::preventStrayRequests()`, so no test reaches the network;
- every secret blanked in `phpunit.xml` (in both `$_SERVER` and `$_ENV`).

So migrations, Blade, Livewire (`Livewire::test()`), routes and the container are the real thing; WordPress is not. The suite proves the application logic and its WordPress contract as faked. It does not prove WordPress' own behaviour, plugin interplay or rendering in a browser. Smoke-test those on the local site.

Convention tests in `tests/Feature/Conventions` scan the codebase (every Blade view compiles, every `<livewire:…>` tag resolves, every block follows the authoring contract), so a rule fails the build instead of living only in a document.

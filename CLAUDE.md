# Alma v8: project instructions

WordPress + Laravel boilerplate for marketing websites: Bedrock, Acorn 6 (Laravel 13
components), Livewire 4 + Flux, ACF Pro (ACF Composer), Tailwind 4, The SEO Framework. It deploys
over SSH to shared hosting.

Theme root: `web/app/themes/alma`. Run theme commands (composer, npm, pest) from there; run
`wp …` / `wp acorn …` from the repository root.

## Hard constraints

- **Shared hosting.** PHP 8.4, MySQL, cron, SSH. No Redis, no queue worker, no Docker, no Node at
  runtime (assets are built in CI and shipped). Don't introduce anything that needs them
  ([ADR 0001](web/app/themes/alma/docs/adr/0001-shared-hosting-target.md)).
- **Never call a third party inside a request.** Use a module's `$listenAfterResponse`. It runs
  after the response through `Deferred`, with no worker needed
  ([ADR 0002](web/app/themes/alma/docs/adr/0002-after-response-work.md)).
- **Laravel middleware does not run on WordPress-rendered pages.** Behaviour every page needs is a
  WordPress hook class, not middleware ([architecture](web/app/themes/alma/docs/architecture.md#two-request-paths)).
- Do not commit or push unless asked.

## Where code goes

Features are **modules**: `app/Domains/<Module>` with a provider extending
`ModuleServiceProvider` that *declares* `$hooks`, `$listen`, `$listenAfterResponse`, `$commands`,
`$deployTasks`, `$migrations` and `$webRoutes`/`$apiRoutes`. Modules are listed in
`config/alma.php`. Use the `alma-module` skill; copy `app/Domains/Lead`'s shape.

| Module | Docs |
| :--- | :--- |
| Content (blocks, patterns-as-pages, editor, assets) | [blocks.md](web/app/themes/alma/docs/blocks.md), [design-system.md](web/app/themes/alma/docs/design-system.md) |
| Seo (robots, noindex, 404s, redirects, TSF settings) | [seo.md](web/app/themes/alma/docs/seo.md) |
| Lead (forms, attribution, notifications, CRM webhook) | [lead.md](web/app/themes/alma/docs/lead.md) |
| Account (login, 2FA, registration) | [account.md](web/app/themes/alma/docs/account.md) |
| Commerce (WooCommerce, optional) / Payment (optional) | [commerce.md](web/app/themes/alma/docs/commerce.md), [payments.md](web/app/themes/alma/docs/payments.md) |
| Ai (MCP abilities for Claude) | [ai.md](web/app/themes/alma/docs/ai.md) |
| Core (deploy, mail, health, security, logs) | [operations.md](web/app/themes/alma/docs/operations.md) |

## Content: reuse before you build

Read [`docs/block-inventory.md`](web/app/themes/alma/docs/block-inventory.md) before writing any
section markup. A variant is an option on an existing block, not a new block. Pages are
`patterns/<slug>.php`, and the page's content is a single
`<!-- wp:pattern {"slug":"alma/<slug>"} /-->`. Never paste expanded block markup into
`post_content`. Use the `alma-blocks` skill. Its rules are enforced by
`tests/Feature/Conventions`.

## Build & checks

These are exactly what CI runs (`.github/workflows/ci.yml`). If they pass locally, CI passes:

```bash
cd web/app/themes/alma
composer check        # Pint --test, Larastan level 5, Pest
npm run build         # required after changing Tailwind classes in views/patterns/app
cd ../../../.. && vendor/bin/pint --test   # root (Bedrock) files
```

- Tests boot the **real** Acorn app on in-memory SQLite with WordPress faked
  (`tests/Support/WordPress`), the network closed and secrets blanked in `phpunit.xml`
  ([ADR 0005](web/app/themes/alma/docs/adr/0005-tests-boot-the-real-app.md)). A provider that throws
  while booting fails the whole suite on purpose: Acorn would otherwise skip it silently.
- Add a module's WordPress fakes in `tests/Support/WordPress/<module>.php`, each guarded by
  `function_exists`.
- New env variable: read it in `config/<module>.php`, document it in `.env.example` and
  [`docs/configuration.md`](web/app/themes/alma/docs/configuration.md), and blank it in
  `phpunit.xml` if it's a secret or a destination.
- Fix Larastan errors; don't baseline them.

## Running things

```bash
bin/setup.sh                      # first-time local install (idempotent)
wp acorn alma:deploy              # post-deploy tasks: migrate, module tasks, rewrites, view cache
wp acorn alma:blocks:inventory    # regenerate docs/block-inventory.md after block changes
bin/pull production               # production DB + uploads → local (never the other way)
```

Deploys: push to `staging`, or tag `v-YYYYMMDD-vN` for production
([deployment.md](web/app/themes/alma/docs/deployment.md)). Outside production, lead notifications
and webhooks only go to destinations set in that environment's `.env`, because a pulled database
holds production's.

## Docs

Everything lives in [`web/app/themes/alma/docs/`](web/app/themes/alma/docs/README.md). Decisions
are in `docs/adr/`, and current problems in [`docs/known-issues.md`](web/app/themes/alma/docs/known-issues.md).
When you change behaviour, update the doc that describes it in the same change.

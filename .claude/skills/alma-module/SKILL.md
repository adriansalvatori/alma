---
name: alma-module
description: >-
  Adds or extends a feature module in an alma project (app/Domains/<Module>): business logic,
  database tables, wp-admin screens, integrations with third-party APIs, webhooks, cron jobs,
  Livewire forms or WP-CLI commands. Use when the user asks for a new capability (bookings,
  quotes, a CRM sync, a payment gateway…), or to wire hooks, events, listeners or deploy tasks.
---

# Alma modules

Reference: `web/app/themes/alma/docs/architecture.md` and the decision records in `docs/adr/`.
Theme paths below are relative to `web/app/themes/alma`. Look at `app/Domains/Lead` first; it
is the most complete example.

## Shape

```
app/Domains/<Module>/
  <Module>ServiceProvider.php   extends App\Infrastructure\Providers\ModuleServiceProvider
  Actions/        one unit of work per class, one public execute()
  Data/           readonly DTOs with fromArray()/toArray()
  Events/ Listeners/
  Models/         Eloquent; table names unprefixed ('alma_quotes'), Acorn adds $wpdb->prefix
  Services/ Support/
  Hooks/ Admin/   WordPress registrars: register() only adds hooks
  Http/ routes/   controllers, middleware, web.php / api.php
  Database/Migrations/
config/<module>.php              every env() the module reads, with a comment
tests/Feature/<Module>/ tests/Unit/<Module>/
docs/<module>.md
```

Register it in `config/alma.php` (`'key' => <Module>ServiceProvider::class`). If it depends on a
plugin or a flag, implement `public static function enabled(): bool`.

## The provider declares; the base class wires

```php
protected array $hooks = [QuotesAdmin::class];                                   // register()-only classes
protected array $listen = [QuoteRequested::class => [RecordQuote::class]];          // in the request (DB only)
protected array $listenAfterResponse = [QuoteRequested::class => [SyncToCrm::class]]; // third parties
protected array $commands = [ExpireQuotesCommand::class];                           // wp acorn …
protected array $deployTasks = [GrantQuoteCapability::class];                       // alma:deploy steps
protected ?string $migrations = __DIR__.'/Database/Migrations';
protected ?string $apiRoutes = __DIR__.'/routes/api.php';                          // under /api
```

## Rules that keep it working on shared hosting

- **Never call a third party inside the request.** Use `$listenAfterResponse` (it runs through
  `Deferred`). The handler records its outcome and never throws.
- No Redis, no worker, no long-running process, no Node at runtime. Scheduled work is WP-Cron
  (schedule on `init` if missing); keep each run short.
- Webhooks you **receive** verify a signature and **fail closed** (503 when the secret isn't
  configured, 403 on mismatch), and are idempotent. Copy `app/Domains/Payment/Support/WebhookSignature.php`.
- Secrets come from `.env` through `config/<module>.php`. Add each new variable to `.env.example`,
  `docs/configuration.md`, and as a blank in `phpunit.xml` (both `<server>` and `<env force>`).
- Settings an editor changes live in one option, behind a settings service with defaults; pick
  one precedence rule (alma: `.env` wins and is shown locked, else wp-admin, else default).
- Outside production, don't deliver to destinations copied from a production database
  (see `LeadSettings::mayDeliverTo()`).
- wp-admin actions check a capability and a nonce. Grant custom capabilities in a deploy task.
- Deploy tasks are idempotent and non-fatal unless the release truly can't run without them.

## Tests

The suite boots the real app on SQLite (migrations run), with WordPress faked
(`tests/Support/WordPress`; add module fakes in `tests/Support/WordPress/<module>.php`, guarded
with `function_exists`) and the network closed (`Http::fake()` what you call). Use
`RefreshDatabase` for tables and `Livewire::test()` for components. A provider that throws while
booting fails the whole suite. That's intended.

```bash
cd web/app/themes/alma && composer check     # Pint, Larastan (level 5), Pest: exactly what CI runs
```

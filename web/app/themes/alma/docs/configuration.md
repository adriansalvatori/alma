# Configuration reference

Every environment variable alma reads, where it is read, and what happens when it is unset.

> **Regenerate this file when you add a variable.** It was built from every `env('…')` call in
> `config/application.php`, `config/environments/`, and the theme's `config/`, `app/`,
> `bootstrap/` and `routes/` (verified 2026-10-17). A variable missing from these tables is not
> read by anything, so setting it does nothing. To list them yourself:
>
> ```bash
> grep -rhoE "env\('[A-Z0-9_]+'" config web/app/themes/alma/{config,app,bootstrap,routes} | sort -u
> ```
>
> Then add the variable to `.env.example` and, if it is a secret, blank it in the theme's
> `phpunit.xml` (both `<server>` and `<env force="true">`), so the test suite never sees a real value.

Where values come from: `.env` at the repository root, then `.env.local` over it (deploys write
`APP_VERSION` there). On a server, `.env` is `shared/.env`, linked into each release. Bedrock reads
it on every request, so an edit takes effect immediately: alma never caches config.

Acorn's own defaults (`vendor/roots/acorn/config/*.php`) also read Laravel's usual variables:
`CACHE_STORE` (default `file`), `SESSION_DRIVER` (`file`), `QUEUE_CONNECTION` (`sync`),
`DB_CONNECTION` (`wordpress`, the WordPress database), `APP_KEY`, `APP_NAME`, `APP_LOCALE`. The
first three are listed below because they matter on shared hosting.

## WordPress and Bedrock (`config/application.php`, `config/environments/*.php`)

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `WP_ENV` | `production` | **Required.** `development`, `staging` or `production`; selects `config/environments/<env>.php`. Unset means production. |
| `WP_ENVIRONMENT_TYPE` | `WP_ENV` | Only to override WordPress' environment type separately. |
| `WP_HOME` | none | **Required.** The site URL, no trailing slash. |
| `WP_SITEURL` | none | **Required.** `${WP_HOME}/wp` (WordPress core lives in `web/wp`). |
| `DB_NAME`, `DB_USER`, `DB_PASSWORD` | none | **Required** unless `DATABASE_URL` is set. |
| `DB_HOST` | `localhost` | |
| `DB_PREFIX` | `wp_` | Acorn's tables (`wp_alma_leads`, `wp_jobs`) follow it. |
| `DB_SSL` | off | Any truthy value connects with `MYSQLI_CLIENT_SSL`. |
| `DATABASE_URL` | none | `mysql://user:pass@host:port/name`, replacing the four `DB_*` values. |
| `AUTH_KEY` … `NONCE_SALT` (8) | none | **Required.** `bin/setup.sh` generates them. |
| `DISABLE_WP_CRON` | `false` | Set `true` **only after** a real cron runs WP-Cron ([deployment.md](deployment.md#cron)). |
| `DISALLOW_INDEXING` | `false` | Production only (`environments/production.php`); development and staging are always noindex. |
| `WP_POST_REVISIONS` | unlimited | A number limits revisions per post. |
| `WP_DEBUG_LOG` | `true` (development) | Development only. Point it outside the document root. |
| `ACF_PRO_KEY` | none | Defines `ACF_PRO_LICENSE` (activates ACF Pro). Composer authenticates separately, via `auth.json`. |

## Application (theme `config/alma.php`, Acorn)

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `APP_KEY` | none | **Required.** `base64:` + 32 random bytes. Encrypts 2FA secrets and signed values; changing it invalidates them. |
| `APP_VERSION` | `dev` | The running release. Written to `<release>/.env.local` by `bin/remote/activate.sh`; do not set it in `shared/.env`. Read by `config/alma.php` and `config/sentry.php` (Sentry's release). |
| `ALMA_DISABLED_MODULES` | empty | Comma-separated module keys to switch off (`content`, `seo`, `lead`, `account`, `commerce`, `payment`). |
| `QUEUE_CONNECTION` | `sync` | `sync` runs deferred work after the response with no worker; `database` queues it for a cron-run `wp acorn queue:work --stop-when-empty`. |
| `CACHE_STORE` | `file` | Keep `file` on shared hosting. `redis` also needs `illuminate/redis` installed. |
| `SESSION_DRIVER` | `file` | |
| `WIRE_NAVIGATE` | `false` | `config/content.php`. Livewire `wire:navigate` page transitions on links. |
| `WEBP_UPLOADS` | `true` | `config/content.php`. |

## Mail (theme `config/mail.php`)

Without `MAIL_HOST`, WordPress uses PHP `mail()`, which shared hosts often drop or send to spam.

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `MAIL_HOST` | none | Turns SMTP on. |
| `MAIL_PORT` | `587` | |
| `MAIL_ENCRYPTION` | `tls` | `tls` (STARTTLS) or `ssl`. |
| `MAIL_USERNAME`, `MAIL_PASSWORD` | none | |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | WordPress' defaults | |

## Observability (theme `config/sentry.php`, `health.php`, `observability.php`, `logging.php`)

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `SENTRY_LARAVEL_DSN` | none (falls back to `SENTRY_DSN`) | Errors reach Sentry only when set. |
| `SENTRY_ENVIRONMENT` | `WP_ENV` | |
| `SENTRY_SAMPLE_RATE` | `1.0` | Share of errors sent. |
| `SENTRY_TRACES_SAMPLE_RATE` | off | Performance tracing costs quota on every request. |
| `HEALTH_TOKEN` | none | Unlocks the details of `GET /api/health/checks` via the `X-Health-Token` header; otherwise only logged-in administrators see details. |
| `HEALTH_CRON_BEHIND_AFTER` | `900` | Seconds a due WP-Cron event may wait before cron counts as behind. |
| `HEALTH_CRON_STALLED_AFTER` | `3600` | …and before it counts as stalled. |
| `HEALTH_DB_SLOW_AFTER_MS` | `500` | A slower `select 1` warns. |
| `HEALTH_FAILED_JOBS_WARN_AT` | `1` | Failed deferred calls in the last day before the check warns. |
| `INTEGRATION_LOG_ENABLED` | `true` | Records outgoing HTTP calls (redacted) for debugging integrations. |
| `INTEGRATION_LOG_HOSTS` | all | Comma-separated host allowlist; `*.example.com` matches subdomains. |
| `INTEGRATION_LOG_RETENTION_DAYS` | `30` | |
| `LOG_CHANNEL` | `daily` | Logs go to `web/app/cache/acorn/logs/` (linked to `shared/logs` on servers). |
| `LOG_STACK` | `daily` | Channels for the `stack` channel. |
| `LOG_LEVEL` | `info` | |
| `LOG_DAILY_DAYS` | `14` | |
| `LOG_DEPRECATIONS_CHANNEL` | `null` | |

## SEO (theme `config/seo.php`)

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `SEO_APPLY_ON_DEPLOY` | `true` | Re-apply The SEO Framework settings from `config/seo.php` on every deploy. |
| `SEO_BLOCK_AI_CRAWLERS` | `false` | Ask AI training crawlers to stay away via robots.txt. |

## Leads (theme `config/leads.php`)

The recipients and the webhook can also be set in wp-admin → Leads → Settings.

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `LEAD_NOTIFICATION_RECIPIENTS` | none | Comma-separated addresses notified of each lead. |
| `LEAD_WEBHOOK_URL` | none | Each lead is POSTed here as JSON. |
| `LEAD_WEBHOOK_SECRET` | none | Signs the webhook body (`X-Alma-Signature`); unsigned without it. |
| `LEAD_RETENTION_DAYS` | module default | Leads older than this are deleted daily; never below 30. |
| `LEAD_STORE_SPAM` | off | Keep spam-guarded submissions (status `spam`) instead of dropping them. |
| `LEAD_BLOCKLIST` | none | Comma-separated emails or domains treated as spam. |
| `LEAD_MIN_FILL_SECONDS` | `3` | Minimum seconds between rendering and submitting the form. |
| `LEAD_RATE_LIMIT` | `5` | Submissions per IP per ten minutes. |

## Commerce and payments (theme `config/commerce.php`, `config/payments.php`)

| Variable | Default | Notes |
| :--- | :--- | :--- |
| `COMMERCE_FAVORITES_MAX` | `50` | Only when WooCommerce is active. |
| `PAYMENTS_ENABLED` | `false` | Enables the Payment module. |
| `PAYMENTS_GATEWAY` | `fake` | The default gateway. |
| `PAYMENTS_CURRENCY` | `USD` | |
| `PAYMENTS_FAKE_SECRET` | derived from `APP_KEY` | The fake gateway's webhook secret (non-production). |
| `WOMPI_PUBLIC_KEY`, `WOMPI_PRIVATE_KEY`, `WOMPI_EVENTS_SECRET` | none | Read only by the commented-out Wompi example in `config/payments.php`. |

## Deploy-time values (not read by the application)

| Where | Variable | Notes |
| :--- | :--- | :--- |
| `.env.deploy` | `<ENV>_SSH_HOST`, `_SSH_USER`, `_SSH_PORT`, `_PATH`, `_URL`, `_WP` | SSH targets for `bin/deploy`, `bin/rollback`, `bin/pull`, `bin/backup` (`.env.deploy.example`). |
| `.env.deploy` | `ALMA_PULL_SCRUB_OPTIONS` | Options deleted after `bin/pull`. |
| CI | `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`, `DEPLOY_SSH_PORT`, `DEPLOY_PATH`, `DEPLOY_URL`, `DEPLOY_WP`, `DEPLOY_SSH_KEY_FILE` | Set by `.github/workflows/deploy.yml` from Environment secrets; override `.env.deploy`. |
| `bin/setup.sh` | `ALMA_SETUP_*` | Preset answers for an unattended setup. |
| `.mcp.json` | `LOCAL_MCP_*`, `PRODUCTION_MCP_*` | Claude Code's WordPress MCP connections, from your shell environment. |

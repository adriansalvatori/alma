# Operations

How to keep an Alma site healthy on shared hosting (cPanel, Apache or LiteSpeed, PHP-FPM, MySQL),
with no shell access required day to day.

## Health

| Endpoint | Purpose | Auth |
| :--- | :--- | :--- |
| `GET /api/health` | Liveness and the running version (`APP_VERSION`) | Public |
| `GET /api/health/checks` | Every check below | Statuses public; details need a token or an admin login |

- **Point an uptime monitor at `/api/health/checks`.** It returns **503** when a check is critical
  (the database is down, or WP-Cron has stalled), so the monitor can alert on the status code.
- **Details are restricted.** Anonymous callers see `ok` / `warning` / `critical` per check. Details
  (latencies, counts, ages, the SMTP host) need the `X-Health-Token` header (or `?token=`) to match
  `HEALTH_TOKEN`, or a logged-in administrator. Leave `HEALTH_TOKEN` empty to allow
  administrators only.

| Check | Warning | Critical |
| :--- | :--- | :--- |
| `database` | `select 1` slower than `HEALTH_DB_SLOW_AFTER_MS` (500) | No answer |
| `cron` | Oldest due event older than `HEALTH_CRON_BEHIND_AFTER` (900 s) | Older than `HEALTH_CRON_STALLED_AFTER` (3600 s) |
| `failed_jobs` | A deferred job failed in the last 24 h | n/a |
| `mail` | No SMTP (`MAIL_HOST` unset) | n/a |
| `error_reporting` | No Sentry DSN | n/a |

The same checks appear in **Tools → Site Health** and, with their evidence, on the dashboard's
**Platform & Infrastructure Health** widget, next to each host the site called in the last week
judged from the [integration log](#integration-log) (cached for a minute; see
[design-system.md](design-system.md#the-dashboard)). Administrators also get a notice on every
admin screen when cron has stalled; it stays on the page rather than going to the toolbar's
notifications drawer.

To add a check, implement `App\Infrastructure\Observability\Health\HealthCheck`. Then either add it
to `HealthChecker::CHECKS`, or tag it `alma.health-checks` from a module provider. A check that
throws is reported as critical; it never breaks the endpoint.

## Cron (do this on every site)

WP-Cron only runs when someone visits. On a quiet site scheduled work (lead retention purges, log
pruning, plugin tasks) falls behind. With `DISABLE_WP_CRON=true` and no system cron, it stops
entirely. Set up a real cron:

1. In cPanel → **Cron Jobs**, add one entry, every 5 minutes. Use one of these two forms.

   **WP-CLI** (preferred when available; Bedrock reads `.env` itself):

   ```
   */5 * * * * cd ~/alma/current && wp cron event run --due-now >/dev/null 2>&1
   ```

   **HTTP** (works everywhere):

   ```
   */5 * * * * curl -fsS https://example.com/wp/wp-cron.php >/dev/null 2>&1
   ```

   **Do not add `?doing_wp_cron`** to the URL. That parameter makes every run a silent no-op.

2. Wait one interval. Check that `/api/health/checks` (or Site Health) reports cron `ok`.
3. **Only then** set `DISABLE_WP_CRON=true` in the server's `.env`. Setting it first stops all
   scheduled work if the cron entry is wrong. Remote Leverage lost scheduled jobs exactly that way.

With `QUEUE_CONNECTION=database`, add a second entry to drain the queue:

```
* * * * * cd ~/alma/current && wp acorn queue:work --stop-when-empty --max-time=50 >/dev/null 2>&1
```

## Logs

- **Application log.** Daily files in Acorn's storage, `web/app/cache/acorn/logs/laravel-YYYY-MM-DD.log`,
  kept for `LOG_DAILY_DAYS` (14) days. `LOG_LEVEL` defaults to `info`.
- **WordPress `debug.log`.** Only in development (`config/environments/development.php`).
- **Never web-readable.** `web/app/.htaccess` denies `/app/cache/` and every `*.log`.
- **Reading them.** Use cPanel's File Manager, SSH, or the deploy workflow's log-tail job.

## Errors: Sentry

Set `SENTRY_LARAVEL_DSN` to report errors to Sentry. Nothing is reported without it, and there is
deliberately no default DSN.

- **Release.** The release is `APP_VERSION`, so every error names the deploy that produced it.
- **No personal data.** `send_default_pii` is off: no IP addresses, cookies or request bodies.
- **Tracing.** Off unless `SENTRY_TRACES_SAMPLE_RATE` is set.
- **Coverage.** `SentryReporting` connects Acorn's exception handler (Acorn routes, Livewire,
  console). It also adds a shutdown hook for fatal errors on pages WordPress renders itself. On
  those pages the Laravel handler never runs, and WordPress' "critical error" screen would
  otherwise hide them.

## Email: SMTP

On shared hosting, PHP's `mail()` is the reason contact-form emails "never arrive". Set the SMTP
variables and every `wp_mail()` goes through your provider:

```
MAIL_HOST=smtp.postmarkapp.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls        # tls (587) or ssl (465)
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Example"
```

- **Credentials are never sent in clear text.** `MAIL_ENCRYPTION=none` is honoured only for local
  catchers (Mailpit, `127.0.0.1`, `*.test`); elsewhere it is upgraded to STARTTLS.
- **Timeout.** 10 seconds.
- **Failures are logged** without recipients or body.

**Launch checklist.** SMTP configured, and the sending domain's SPF and DKIM records published.

## Integration log

**Tools → Integration log** lists every outbound HTTP call the site made: from Laravel's `Http`
client and from WordPress' `wp_remote_*`. It shows method, URL, status, duration and the response
body. It answers "did the lead reach the CRM, and what did it say?" without a shell.

- **Secrets are redacted before storage.** That covers headers, query parameters and JSON keys
  matching `config/observability.php` → `redact` (authorization, token, secret, password, key, …).
- **Bodies are capped** at 64 KB.
- **Host filtering.** WordPress.org update checks, Sentry and loopback calls are never recorded. Set
  `INTEGRATION_LOG_HOSTS` to record only some hosts.
- **Retention.** A daily WP-Cron job prunes rows older than `INTEGRATION_LOG_RETENTION_DAYS` (30).
- **It never fails the call it records.** That holds even before the table exists.

Disable it with `INTEGRATION_LOG_ENABLED=false`.

## Caching

Acorn's cache store defaults to files, which works on every host. For code that must keep working
when the cache does not, use `App\Infrastructure\Cache\SoftCache`. Examples: a form submission, a
webhook, a rate limit that must not lock everyone out. A cache error then reads as a miss and the
write is skipped. Shared hosts break file caches in mundane ways: a full disk or quota, or a
permissions change after a deploy.

## Page caches (LiteSpeed Cache, host caches, Cloudflare)

Two rules, both learned in production at Remote Leverage:

1. **Never cache a response that sets a cookie.** A cached `Set-Cookie: laravel_session` is replayed
   to every visitor, and they all share one session, which means one login. Exclude `/login`,
   `/register`, `/dashboard`, `/settings`, `/two-factor-challenge`, `/livewire/*`, `/api/*`,
   `/wp-admin/*` and `/wp-json/*`. Also turn on the cache's "do not cache responses with
   Set-Cookie" option where it exists.
2. **Let the `X-Livewire` header reach PHP.** Livewire 4 rejects updates without it, which looks
   like a 404 on every interaction. Some CDN and WAF rules strip unknown headers.

## Security baseline

The **core** module applies these on every site:

- **XML-RPC.** Disabled completely: no methods, no `X-Pingback` header, and blocked in `.htaccess`.
- **Version leaks.** The WordPress version, RSD, WLW and shortlink tags are removed, along with the
  `X-Powered-By` headers Acorn and PHP add.
- **User enumeration.** `?author=N` is a 404 for visitors, and the REST users endpoints are hidden
  from anonymous requests.

The **`.htaccess` files** (Apache/LiteSpeed; port them by hand for nginx):

| File | Does |
| :--- | :--- |
| `web/.htaccess` | Authorization header passthrough (Application Passwords, MCP); denies dotfiles, logs, dumps, `composer.*`, `package*.json`, `xmlrpc.php`; security headers; compression; one-year caching of fingerprinted build assets; the WordPress front controller; an optional HTTPS redirect (commented) |
| `web/app/.htaccess` | Denies `/app/cache/` (Acorn views, sessions, logs), PHP in uploads, and PHP inside plugins' and themes' `vendor/` |
| `web/app/uploads/.htaccess` | No script execution in uploads (for hosts where uploads is a symlink, copy it into the shared uploads directory) |
| `web/app/themes/alma/.htaccess` | Serves only `public/`, `style.css` and `screenshot.png`. Everything else in the theme (vendor, tests, Blade views, `composer.json`, docs) is a 403 |

**Before `.htaccess` matters at all:** the document root must be `web/` (or `current/web` with the
deploy layout), never the project root.

If Application Passwords or the MCP adapter fail with 401 even with the right credentials, the host
is still dropping the Authorization header. Uncomment `CGIPassAuth On` in `web/.htaccess`; it needs
Apache 2.4.13+ and `AllowOverride AuthConfig`, and without those it is a 500 error. Also check that
no security plugin disables Application Passwords (Wordfence does by default).

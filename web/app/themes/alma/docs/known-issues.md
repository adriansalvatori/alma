# Known issues

What is wrong or unverified right now. Strike an entry through with the date when it's fixed,
and move it to the bottom after a while.

## Setup

1. **ACF Pro is not in `composer.lock` yet.** It is required in `composer.json`, but the lock was
   built without credentials. Add `auth.json` (see `auth.json.example`) and run
   `composer update wpengine/advanced-custom-fields-pro`, then commit the lock. Until then,
   `composer install` warns that the lock is out of date, and ACF must be present as a manually
   installed plugin. CI fails with a clear message if the lock contains ACF and the `ACF_PRO_KEY`
   secret is missing.

## Unverified on real infrastructure

2. **The `.htaccess` files** (web root, `web/app`, uploads, theme) have only static tests. Check
   them on the first real Apache/LiteSpeed host. `CGIPassAuth On` is commented out because it 500s
   on hosts without Apache 2.4.13+ / `AllowOverride AuthConfig`; the `SetEnvIf` fallback passes the
   Authorization header instead (needed for Application Passwords and MCP).
3. **The CI and deploy workflows** have not run on GitHub yet. The deploy scripts were tested
   end to end against a simulated server. `actionlint` and `shellcheck` were not available.
4. **Sentry** has not been exercised with a real DSN.
5. **The editor JavaScript** (the pattern page notice, Detach from pattern, the page inserter list)
   has not been checked in a browser.
6. **WooCommerce:** the Commerce components, Woo template overrides and the payment gateway bridge
   have not been run against a real WooCommerce install.

## Behaviour to know about

7. **A new release can report its old version for up to about 2 minutes.** PHP's realpath cache
   keeps resolving `current` to the previous release. The deploy smoke check retries for 3 minutes.
8. **Pest prints about 6 warnings** (`unlink(...framework/views/...)`) from Livewire compiling
   single-file components. They come from vendor code, `phpunit.xml` restricts failures to our own
   code, and the run exits 0.
9. **Deferred work under mod_php.** Without `fastcgi_finish_request()`/`litespeed_finish_request()`
   the after-response work still runs, but the browser waits for it. PHP-FPM or LiteSpeed avoid this.

## Deferred on purpose

- The page-copy overlay (copy editing on pattern pages without detaching): [ADR 0006](adr/0006-patterns-as-pages.md).
- Canvas inline editing (`blockVersion` 3), which needs ACF 6.8+ and is tied to the overlay.
- A copy-first block sidebar (RL's `BlockSidebar`).

# 0002. Third-party calls run after the response, with or without a worker

- **Status:** accepted
- **Date:** 2026-10-17

## Context

A lead submission notifies the team and posts to a CRM. Doing that inside the request makes the visitor wait on someone else's API, and a slow or failing API breaks the form. Shared hosting has no queue worker. Acorn only calls `terminate()` on routes it handles itself, so Laravel's `dispatchAfterResponse()` silently drops work on WordPress-rendered pages. RL measured this in production.

## Decision

- Listeners that call third parties are declared in a module's `$listenAfterResponse` and run through `App\Infrastructure\Queue\Deferred`.
- With `QUEUE_CONNECTION=sync` (default), `Deferred` uses `dispatchAfterResponse()`. With any other connection, it queues.
- `ThemeServiceProvider::ensureApplicationTerminates()` calls `$app->terminate()` on WordPress' `shutdown` hook, after `fastcgi_finish_request()` / `litespeed_finish_request()`, with a guard against running twice.
- Deferred handlers record their outcome and never throw out of the after-response path.

## Consequences

The visitor never waits on integrations, and no worker is needed. Under mod_php (no finish-request function) the work still runs, but the browser waits for it. A failed delivery is not retried by default (`CallHandlerJob::$tries = 1`), because most handlers aren't idempotent. It is visible in the module's activity log instead.

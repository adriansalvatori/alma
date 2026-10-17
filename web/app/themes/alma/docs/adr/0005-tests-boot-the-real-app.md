# 0005. Tests boot the real Acorn application on SQLite, with WordPress faked

- **Status:** accepted
- **Date:** 2026-10-17

## Context

RL's suite never booted Acorn: a hand-written container, hand-created tables and a 73 KB fake of WordPress, with high drift risk and no `Livewire::test()`. Booting real WordPress needs a database server and is slow.

## Decision

`tests/TestCase.php` extends Laravel's testing `TestCase` (Acorn ships it) and creates the application from `bootstrap/application.php`, the same builder `functions.php` boots. The database is in-memory SQLite, so the real migrations run. WordPress functions are faked in `tests/Support/WordPress`, against an in-memory store reset per test. The network is closed with `Http::preventStrayRequests()`, and every secret is blanked in `phpunit.xml`.

## Consequences

Migrations, routes, Blade, Livewire, validation, encryption and the container are real; only WordPress is fake. The suite needs no services and runs in seconds. It does not prove WordPress' own behaviour: a smoke check on the local site stays part of the workflow. A fake that drifts from core is a test bug; keep each fake as close to core as its tests need, and no closer.

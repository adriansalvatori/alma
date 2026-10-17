# 0003. Features are modules with declarative providers

- **Status:** accepted
- **Date:** 2026-10-17

## Context

A boilerplate is copied into projects that need different subsets of it: some sell, some only capture leads, some need accounts. RL's bounded contexts (`app/Domains/*`) kept logic out of Blade and Livewire, but their wiring was spread across a central provider and `setup.php`.

## Decision

- Each feature is a module: `app/Domains/<Name>` with a provider extending `ModuleServiceProvider`.
- The provider declares `$hooks`, `$listen`, `$listenAfterResponse`, `$commands`, `$deployTasks`, `$migrations`, `$webRoutes` and `$apiRoutes`; the base class wires them.
- `config/alma.php` lists modules. `ALMA_DISABLED_MODULES` switches one off per environment, and a provider's static `enabled()` checks its prerequisites (WooCommerce, the Abilities API, a config flag).
- Hook classes expose `register()` and only add hooks there.

## Consequences

A module's whole hook and event graph is readable at the top of one file, and removing a module is deleting a folder and a config line. Modules may call each other's public services (AI reads Lead's models) but never write each other's tables.

# 0001. Shared hosting with SSH is the deployment target

- **Status:** accepted
- **Date:** 2026-10-17

## Context

Alma's patterns come from Remote Leverage v2, which runs on Docker, ECS, EFS, Secrets Manager and CloudWatch. Alma is a boilerplate for many smaller marketing sites, most of which live on cPanel-style hosting: PHP-FPM or LiteSpeed, MySQL, a cron entry and SSH, but no Redis, no queue worker, no containers and no Node at runtime.

## Decision

Everything in alma must run on that baseline:

- Assets are built in CI and shipped; the server never runs Node.
- Deferred work runs after the response (`Deferred`, ADR 0002); a database queue drained by cron is optional.
- Caches, sessions and rate limiters use Acorn's file/array stores. Redis is never assumed.
- Secrets live in a server-side `.env` in the `shared/` directory, linked into each release.
- Deploys are rsync over SSH into release folders with an atomic `current` symlink switch.
- Web-server rules ship as `.htaccess` (Apache/LiteSpeed).

## Consequences

AWS-specific patterns from RL (Secrets Manager, EFS, CloudWatch log tailing, ECS exec) are not ported. Their intent is kept with portable equivalents: `.env`, `shared/uploads`, the health endpoint and log files, SSH. A project that outgrows shared hosting can add Redis or a worker by configuration, not code.

# Alma v8

A WordPress + Laravel boilerplate for marketing websites that deploy to shared hosting.

Every project starts on a foundation that has already been through production: Alma v8 carries
the lessons of the Remote Leverage rebuild, minus the AWS/Docker infrastructure, so it runs
anywhere with PHP 8.4, MySQL, cron and SSH.

## What you get

| | |
| :--- | :--- |
| **Stack** | Bedrock · WordPress 7.1 · Acorn 6 (Laravel 13) · Livewire 4 + Flux · ACF Pro (ACF Composer) · Tailwind 4 · Vite 8 |
| **Content** | Code-first ACF blocks with an enforced authoring contract, pages as patterns in git, a curated editor, a build-time image pipeline |
| **Leads** | Lead form block, first/last-touch attribution, spam protection, email notifications, signed CRM webhook, wp-admin screens, retention and privacy tools |
| **SEO** | The SEO Framework with settings in code, robots.txt, noindex markers, real 404s, two-tier redirects |
| **Accounts** | Login, registration and 2FA enforced at WordPress authentication (wp-admin included) |
| **Optional** | WooCommerce integration; a gateway-agnostic payment module; MCP abilities so Claude can operate the site |
| **Operations** | Post-deploy command, health endpoints, cron health, Sentry, SMTP, outbound-call log, hardened `.htaccess` |
| **Delivery** | CI (Pint, Larastan, Pest, Vite), SSH release deploys with atomic switch and rollback, `bin/pull` for production → local |

## Quick start

```bash
cp auth.json.example auth.json        # add your ACF Pro license key
bin/setup.sh                          # .env, dependencies, database, WordPress install, first deploy
cd web/app/themes/alma && npm run dev
```

Then work through [local development](web/app/themes/alma/docs/local-development.md). Before the
first deploy, follow [deployment](web/app/themes/alma/docs/deployment.md) and its launch checklist.

## Checks

```bash
cd web/app/themes/alma && composer check && npm run build
```

`composer check` runs Pint, Larastan and the Pest suite. The suite boots the real application on
SQLite, with WordPress faked and the network closed, and runs in seconds with no services.

## Documentation

All docs live in [`web/app/themes/alma/docs`](web/app/themes/alma/docs/README.md): architecture,
one guide per module, configuration, deployment, operations, decision records and known issues.
Working with Claude Code? [`CLAUDE.md`](CLAUDE.md) and the skills in `.claude/skills/` load
automatically.

## License

MIT. Built on Roots Bedrock and Sage.

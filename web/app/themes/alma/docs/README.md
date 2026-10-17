# Alma documentation

Start with [architecture.md](architecture.md), then the doc for the module you're working in.
Agent-facing rules are in the repository's [`CLAUDE.md`](../../../../../CLAUDE.md) and the skills
in `.claude/skills/`.

## Reference

| Document | Covers |
| :--- | :--- |
| [architecture.md](architecture.md) | Boot, the two request paths, modules, after-response work, deploy tasks, tests |
| [configuration.md](configuration.md) | Every environment variable: where it's read, its default |
| [local-development.md](local-development.md) | Setup, commands, what the test suite proves and doesn't |
| [deployment.md](deployment.md) | SSH release deploys to shared hosting, cron, rollback, launch checklist |
| [operations.md](operations.md) | Health, logs, Sentry, SMTP, integration log, `.htaccess`, page-cache rules |

## Modules

| Document | Module |
| :--- | :--- |
| [blocks.md](blocks.md) · [design-system.md](design-system.md) · [block-inventory.md](block-inventory.md) | Content |
| [seo.md](seo.md) | Seo |
| [lead.md](lead.md) | Lead |
| [account.md](account.md) | Account |
| [commerce.md](commerce.md) · [payments.md](payments.md) | Commerce, Payment (optional) |
| [ai.md](ai.md) | Ai |

## Decisions and problems

- [adr/](adr/): architecture decision records. Copy `0000-template.md` for a new one.
- [known-issues.md](known-issues.md): what is wrong or unverified right now.

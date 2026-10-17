# AI access (Claude over MCP)

The AI module lets Claude (claude.ai, Claude Desktop, Claude Code, or any MCP client) work on the
site: read pages and leads, draft posts, upload media, manage redirects. It builds on two
WordPress pieces rather than a bespoke API:

- the **Abilities API** in core (WordPress 6.9+), where each thing Claude can do is a registered
  ability with an input schema, an output schema and a permission check;
- the **MCP adapter** plugin (`wordpress/mcp-adapter`, installed by Composer), which serves those
  abilities over MCP at `/wp-json/mcp/mcp-adapter-default-server`.

Code: `app/Domains/Ai`. Configuration: `config/ai.php`. Admin: **Settings → AI Access**.

## What is exposed

| Ability | Does | Needs |
| :--- | :--- | :--- |
| `alma/site-health` | Release, environment, versions, plugins, modules, cron health, health checks | `edit_pages` |
| `alma/list-pages` | Pages with status, URL and the pattern a page is built from | `edit_pages` |
| `alma/describe-page` | One page: code-owned or detached, and the blocks it uses | `edit_pages` |
| `alma/list-patterns` | The theme's page and section patterns | `edit_pages` |
| `alma/list-blocks` | The `acf/*` blocks and their fields | `edit_pages` |
| `alma/create-draft-post` | A post from text or light Markdown, as real blocks; draft by default | `edit_posts` (`publish_posts` to publish) |
| `alma/list-media` | Search the media library | `upload_files` |
| `alma/upload-media` | Add an image or PDF from a URL or base64 | `upload_files` |
| `alma/lead-stats` | Lead counts by status, form and source | `alma_manage_leads` |
| `alma/query-leads` | Find leads (personal data) | `alma_manage_leads` |
| `alma/update-lead-status` | Move a lead through the pipeline (logged in its activity) | `alma_manage_leads` |
| `alma/list-redirects` | Managed and code redirects | `manage_options` or `alma_manage_redirects` |
| `alma/create-redirect` | Add a redirect through the SEO module's guards | same |
| `alma/delete-redirect` | Remove a managed redirect, in two confirmed steps | same |

Lead abilities register only when the Lead module is on; redirect abilities only with the SEO
module.

### Adding an ability

1. Create a class under `app/Domains/Ai/Abilities/<Group>/` extending `Abilities\Ability`:
   `name()` (`alma/...`), `label()`, `description()` (agents choose tools by reading it, so say
   when to use it), `category()`, `inputProperties()`, `permission()` and `execute()`.
   Set `readOnly()`, `destructive()` and `idempotent()` honestly.
2. Add one line to `abilities` in `config/ai.php`.
3. Add a test in `tests/Feature/Ai`. `ai_test_run_ability('alma/x', [...])` runs it the way core
   does: permission filter, execute callback, result filter.

Call the owning module's services; never write another module's tables directly. Redirects go
through `RedirectManager`, and lead status through `ChangeLeadStatusAction`.

## Safety

- **Every ability checks its own capability.** What Claude can do is decided by the WordPress
  user it authenticates as. Nothing is granted to "AI" as such.
- **Destructive abilities confirm in two steps.** The first call returns a preview and a
  confirmation token; only a repeat of the same call carrying the token does the work. The token is
  an HMAC over the ability, the user and the exact input, valid for 10 minutes, and nothing is
  stored. A token cannot be replayed on different input or by another user.
- **Every call is logged** in `alma_ai_tool_calls`: user, ability, outcome (succeeded, failed,
  denied, invalid, awaiting confirmation), duration, and a redacted input summary. Secrets, tokens
  and file contents are never stored, and long text is cut. The last 50 calls show in
  Settings → AI Access. Rows older than 90 days are pruned on roughly one write in 50, so no cron
  job is needed.
- **Uploads** are fetched through `download_url()`, which refuses private and loopback addresses.
  Types are checked against the file's real contents and limited to JPEG, PNG, GIF, WebP and PDF.
  SVG is excluded because it can carry script and would be served from this site's origin. The
  size cap is `AI_MEDIA_MAX_MB` (default 10).

## The agent user and credentials

Claude acts as one WordPress user, `ai-agent` (`AI_AGENT_LOGIN`), an **editor**. Capability
groups are switched on or off in `.env`:

| Variable | Default | Grants |
| :--- | :--- | :--- |
| `AI_AGENT_CAN_UPLOAD_MEDIA` | `true` | `upload_files` |
| `AI_AGENT_CAN_PUBLISH` | `false` | `publish_posts`, `publish_pages` |
| `AI_AGENT_CAN_EDIT_PUBLISHED` | `false` | `edit_published_*`, `delete_published_*` |
| `AI_AGENT_CAN_READ_LEADS` | `false` | `alma_manage_leads` (all lead abilities) |
| `AI_AGENT_CAN_MANAGE_REDIRECTS` | `false` | `alma_manage_redirects` (redirect abilities only) |

A flag that is off is an **explicit deny** on the user (`add_cap($cap, false)`), not just a
missing grant. An editor can publish by role, so without the deny the agent could too.

`wp acorn alma:deploy` re-applies the flags on every release, so turning one off revokes access on
the next deploy. Deploy **never issues a credential**: deploy output gets logged, and a password
printed there is a leaked password.

Credentials are **Application Passwords**, one per person and client, so each can be revoked on
its own:

- **Settings → AI Access → Issue credential.** The plaintext is shown once.
- `wp acorn alma:ai:agent --issue="Ada, Claude Desktop"`, plus `--list` and `--revoke=<uuid>`.

## Connecting a client

The endpoint is `https://<site>/wp-json/mcp/mcp-adapter-default-server`.

**Claude Desktop / Claude Code.** Use the STDIO proxy (needs Node.js on that computer). Settings →
AI Access prints this block ready to paste, with the password filled in right after you issue it.

```json
{
  "mcpServers": {
    "my-site": {
      "command": "npx",
      "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "https://example.com/wp-json/mcp/mcp-adapter-default-server",
        "WP_API_USERNAME": "ai-agent",
        "WP_API_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx"
      }
    }
  }
}
```

For Claude Code, the repo's `.mcp.json` reads these from environment variables instead of holding
them.

**claude.ai.** Add a custom connector with the endpoint URL and an `Authorization: Basic …` request
header (base64 of `ai-agent:<application password>`). Whether request headers are allowed depends
on your organisation's settings; where they are not, use Claude Desktop.

## Prerequisites and pitfalls

Settings → AI Access checks each of these:

1. **HTTPS.** WordPress only offers Application Passwords over HTTPS, unless
   `WP_ENVIRONMENT_TYPE=local`.
2. **Application Passwords enabled.** Some security plugins turn them off; Wordfence does by
   default.
3. **The MCP adapter can load.** Installed by Composer, its classes come from the project's
   autoloader, and the plugin only uses that when `WP_MCP_AUTOLOAD` is defined as `false` in
   `config/application.php`. Without it, the plugin looks for its own `vendor/`, fails, and raises a
   notice on every request (a 500 in development). The deploy step that activates the plugin
   refuses to do so until the constant is defined.
4. **The Authorization header reaches PHP.** On shared hosting running PHP as CGI/FastCGI, Apache
   often strips it, and Claude gets a 401 that looks exactly like a wrong password. The shipped
   `web/.htaccess` passes it through (`CGIPassAuth On` plus a `SetEnvIf`/rewrite fallback). The
   **Check the Authorization header** button sends the site a request carrying the header and
   reports whether PHP saw it.

## Before granting production access

- **Edits are live immediately.** There is no review step between an agent's call and the site.
  Keep `AI_AGENT_CAN_PUBLISH` and `AI_AGENT_CAN_EDIT_PUBLISHED` off unless someone is accountable
  for what the agent changes.
- **Pattern pages are code-owned.** A page whose content is a single `wp:pattern` reference is
  built from `patterns/<slug>.php` in git. `describe-page` reports this; change the pattern file,
  not the page.
- **Leads are personal data.** `AI_AGENT_CAN_READ_LEADS` exposes names, emails and phone numbers
  to whoever holds a credential. Grant it deliberately.

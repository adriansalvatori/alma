# Leads

The Lead module (`app/Domains/Lead`) is the core of a marketing site. It captures form submissions, records where each visitor came from and filters spam. It also emails the team, posts every lead to a CRM webhook, and keeps an audit trail of every step. Leads are managed in **wp-admin → Leads**.

Nothing in the module needs a queue worker, Redis or an external service, so it runs as-is on shared hosting.

## Placing a form

- **In the editor:** add the **Lead Form** block. Its sidebar sets the heading, intro, which fields to ask for, consent, button label, success message, redirect URL and a *form key* (`contact`, `pricing-demo`...). The form key groups leads in wp-admin.
- **In a template:**

  ```blade
  <livewire:lead.form form-key="pricing" :fields="['phone', 'company', 'message']" :require-consent="true" />
  ```

  Optional fields are `phone`, `company` and `message`; name and email are always asked for.

Everything that configures the form is `#[Locked]` in the Livewire component. A visitor can change what they type, but never which form it counts as or where it redirects.

## Data model

| Table | What it holds |
| :--- | :--- |
| `wp_alma_leads` | One row per submission: name, email, phone, company, message, `fields` (JSON for extra fields), `form_key`, consent + `consented_at`, `attribution` (JSON, first and last touch), `ip_hash` (salted HMAC, never the raw IP), `user_agent`, `status` |
| `wp_alma_lead_activity` | The audit log: `lead_id`, `event`, `actor`, `outcome` (`dispatched`, `succeeded`, `failed`, `skipped`), `detail` (JSON) |

Statuses: `new`, `contacted`, `qualified`, `won`, `lost`, `spam`. Spam stays out of the default list view.

## Event flow

```
Lead Form (Livewire)
  │  validate → SpamGuard::inspect()
  │     spam?  → same success message to the bot; stored as "spam" only if "store spam" is on
  ▼
CaptureLeadAction::execute(LeadData)
  │  validate again, normalise, stamp attribution from the cookie, store
  ▼
event LeadCaptured ─┬─ RecordLeadDispatch        sync, in the request:  activity "dispatched"
                    │
                    │  ── response sent to the visitor ──
                    │
                    ├─ SendLeadNotification      after response:  activity succeeded/failed/skipped
                    └─ SendLeadWebhook           after response:  activity succeeded/failed/skipped
```

The after-response listeners go through `App\Infrastructure\Queue\Deferred`. With `QUEUE_CONNECTION=sync` (the default) they run once the page has been delivered. With `QUEUE_CONNECTION=database` they move onto the jobs table instead. Neither listener ever throws: it logs, writes a `failed` outcome row, and moves on.

**Reading the activity log.** Every lead should show one `dispatched` row followed by one outcome row per listener. A `dispatched` row with no outcomes means the after-response work never ran (see "after-response work" in `docs/architecture.md`).

## Attribution

`Hooks\AttributionCookie` runs on `template_redirect` for front-end page views. A visit counts as a *touch* when its URL carries `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `gclid`, `fbclid` or `msclkid`, or when it arrives from another site; a referral with no tags becomes `source = referring host, medium = referral`. Direct and internal visits change nothing.

- The **first touch** is kept for as long as the cookie lives (90 days).
- The **last touch** is replaced by every new touch.

The cookie (`alma_attribution`) is first-party, HttpOnly, SameSite=Lax and set server-side, with no JavaScript. It records where a visit came from, never who the visitor is. Values are stripped of markup and capped at 255 characters (500 for URLs). The lead stores the attribution in effect when the form is submitted.

## Spam protection

`Services\SpamGuard`, with no captcha and no third party:

| Check | How |
| :--- | :--- |
| Honeypot | A `website` field positioned off-screen. Anything in it means a bot. |
| Fill time | The form carries its render time, encrypted with `APP_KEY`. Submitting faster than `LEAD_MIN_FILL_SECONDS` (default 3) means a bot. There is no maximum age, because a page cache serves old tokens. |
| Rate limit | `LEAD_RATE_LIMIT` attempts (default 5) per IP per 10 minutes, counted on every attempt. If the cache is unavailable, the check is skipped. |
| Blocklist | Emails or domains (subdomains included) from Leads → Settings or `LEAD_BLOCKLIST`. |

A bot gets exactly the response a person gets, so it learns nothing about which check caught it.

## Settings and precedence

Leads → Settings covers notification recipients, webhook URL and secret, retention, store-spam, blocklist and success message.

**One rule for every key:** a value set in `config/leads.php` (normally through `.env`) wins, otherwise the value saved in wp-admin, otherwise the built-in default. A field pinned by `.env` is shown read-only, along with the variable that sets it.

| Variable | Setting |
| :--- | :--- |
| `LEAD_NOTIFICATION_RECIPIENTS` | Comma-separated emails (default: the site admin email) |
| `LEAD_WEBHOOK_URL` | Where every lead is POSTed |
| `LEAD_WEBHOOK_SECRET` | Signs the webhook body |
| `LEAD_RETENTION_DAYS` | Retention window (minimum 30, default 365) |
| `LEAD_STORE_SPAM` | Keep spam as status `spam` (`true`/`false`) |
| `LEAD_BLOCKLIST` | Comma-separated emails or domains |
| `LEAD_MIN_FILL_SECONDS` | Spam guard fill time (default 3) |
| `LEAD_RATE_LIMIT` | Submissions per IP per 10 minutes (default 5) |
| `LEAD_DELIVERY` | `auto` / `always` / `never`; see below |

Use `.env` to pin a value per environment, for example so staging posts to a test webhook.

### Outside production: database destinations are ignored

A local or staging database is often a copy of production's (`bin/pull`), so the recipients and webhook URL it holds are production's. A test lead on a laptop would otherwise reach the real inbox and the real CRM. So outside production (`wp_get_environment_type() !== 'production'`) the notification and the webhook only go to destinations set in that environment's own `.env`. Everything else is recorded as **skipped**, with the reason, in the lead's activity log.

| `LEAD_DELIVERY` | Behaviour |
| :--- | :--- |
| `auto` (default) | Production delivers everywhere; other environments only to `.env` destinations |
| `always` | Deliver to every configured destination, in any environment |
| `never` | Deliver nothing, in any environment (leads are still stored) |

The **Send test webhook** button is an explicit admin action and is not subject to this rule.

## Connecting a CRM (the webhook)

Set a webhook URL and every lead is sent as:

```http
POST <your URL>
Content-Type: application/json
User-Agent: Alma/<release>
X-Alma-Signature: t=1792217175,v1=5f0c...e1

{
  "event": "lead.captured",
  "site": "https://example.com/",
  "lead": {
    "id": "6a1f...uuid", "name": "Ada Lovelace", "email": "ada@example.com",
    "phone": null, "company": "Analytical Engines", "message": "Hello",
    "fields": {}, "form": "contact", "consent": true, "consented_at": "2026-10-17T12:00:00+00:00",
    "attribution": { "first": { "source": "google", "medium": "cpc", ... }, "last": { ... } },
    "status": "new", "created_at": "2026-10-17T12:00:00+00:00"
  }
}
```

Zapier ("Catch Hook"), Make ("Custom webhook") and n8n ("Webhook" node) accept this directly. The request times out after 10 seconds, and any non-2xx answer is recorded as `failed` on the lead. **Send test webhook** on the settings screen posts a sample lead (event `lead.test`) right away.

### Verifying the signature on the receiving side

The header is `t=<unix time>,v1=<hex HMAC-SHA256 of "<t>.<raw body>">`, keyed with the webhook secret. Reject anything that doesn't match, or whose timestamp is more than five minutes old.

```php
function alma_signature_valid(string $body, string $header, string $secret): bool
{
    if (! preg_match('/^t=(\d+),v1=([a-f0-9]{64})$/', $header, $m) || abs(time() - (int) $m[1]) > 300) {
        return false;
    }

    return hash_equals(hash_hmac('sha256', $m[1].'.'.$body, $secret), $m[2]);
}
```

```js
// Node
const crypto = require('crypto');
function almaSignatureValid(body, header, secret) {
  const m = /^t=(\d+),v1=([a-f0-9]{64})$/.exec(header || '');
  if (!m || Math.abs(Date.now() / 1000 - Number(m[1])) > 300) return false;
  const expected = crypto.createHmac('sha256', secret).update(`${m[1]}.${body}`).digest('hex');
  return crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(m[2]));
}
```

Compute the HMAC over the **raw** request body, before any JSON parsing.

### A dedicated integration instead of the webhook

To call a CRM's API directly, add a listener in the module and register it in `LeadServiceProvider::$listenAfterResponse` under `LeadCaptured::class`. Follow `SendLeadWebhook`: use Laravel's `Http` (fakeable in tests), set a timeout, record an outcome with `LeadActivityLogger`, and never throw.

## Adding a field

- **One-off extra fields** (budget, service...): add the input to `resources/views/components/lead/⚡form.blade.php` and pass it in `LeadData`'s `fields` array. It is stored in the `fields` JSON column, and the email, the admin detail view, the CSV export and the webhook all pick it up with no migration. Keys starting with `_` are internal and hidden from people.
- **A first-class column you filter or report on:** add a migration in `app/Domains/Lead/Database/Migrations`, the property on `LeadData`, the validation rule in `CaptureLeadAction`, and the key in `Lead::toWebhookPayload()` and `LeadCsv::COLUMNS`.

## wp-admin

**Leads** has three tabs: the list, the activity log and settings. Every figure leaves spam out
unless you filter for it.

| Screen | Shows |
| :--- | :--- |
| All leads (`admin.php?page=alma-leads`) | KPI cards that filter the list when clicked (total, awaiting reply, qualified, won with its share of submissions, audit log events). A filter toolbar (search, status, form, first-touch channel, last 24 hours / 7 / 30 days). Then every lead, newest first: date and age, the person with their company's logo, phone and company, the form, a status badge, the first touch (source / medium, campaign), how many audit events it has, and a way in. 20 per page |
| A lead (`&lead=<id>`) | Header with status, form, capture time and id; update the status, reply by email, delete the lead. Cards for the contact and submission (with any extra fields), both attribution touches (click ids shortened, referrer, landing page), and the device. On the right the **execution & audit timeline**: every activity row with its actor, outcome and payload, scrolling on its own beside the details |
| Activity log (`admin.php?page=alma-lead-activity`) | Every activity row across all leads, newest first, filterable by event, actor and outcome (both read off the table, so a new integration is filterable the day it starts logging), with each row's lead and payload. 30 per page |
| Settings | See [Settings and precedence](#settings-and-precedence) |

**Export CSV** downloads exactly the filtered list. Values are escaped so a spreadsheet never runs
one as a formula, and the file carries a UTF-8 BOM so Excel reads accents correctly. **Run retention
purge** runs tonight's purge now (same window, same 30-day floor). Every status change is logged
with the user who made it; deleting a lead removes its activity too.

The avatar is the favicon of the lead's email domain, loaded by the admin's browser from Google's
favicon service (`LeadAvatar`). Only the domain is sent, never the address or a hash of it;
mailbox providers (Gmail, Outlook…) get tinted initials and no request at all; an unknown domain
answers 404 and the initials show through.

**The dashboard** gets four widgets in its left column (`LeadDashboard`):

| Widget | Shows |
| :--- | :--- |
| Lead performance: last 7 days | Submissions, qualified (qualified or won), won and the conversion rate, all for the same cohort: leads captured in the window, whatever they became since. The funnel (captured → contacted → qualified → won) and the daily volume chart cover the same window |
| Hourly lead trend | Leads per day at each hour of the site's clock (Settings → General), for this month, last month or the last 7 days (`?alma_hourly_range=`, so a view can be bookmarked). Averaged per day because early in a month the hours haven't all had the same number of days; busiest and quietest hour underneath |
| Traffic acquisition & attribution channels | Each first-touch source's share of the week's leads (a referring site's host when there was no campaign, "Direct" when there was neither), each linking to the list filtered to it |
| Recent captured leads | The latest six, with form, status and age |

The module's card on the dashboard's Modules widget carries its live counts, and its shortcuts
(lead hub, activity trail, export) join the dashboard's. The toolbar's brand menu gets "Leads &
submissions". Figures are cached for three minutes and cleared whenever a lead is saved or
deleted (`Lead::flushMetrics()`).

Access requires the `alma_manage_leads` capability. `wp acorn alma:deploy` grants it to administrators and editors on every deploy.

## Retention

Leads older than the retention window are deleted daily by WP-Cron (`alma_leads_purge`), along with their activity rows, in batches. Purge on demand with:

```bash
wp acorn alma:leads:purge --dry-run   # count only
wp acorn alma:leads:purge
```

The window can never go below 30 days, whatever is configured. On shared hosting, run WP-Cron from a real cron job (see `docs/deployment.md`) so the purge also happens on quiet sites.

## Privacy

The module registers with WordPress' own tools: **Tools → Export Personal Data** includes a person's leads, and **Tools → Erase Personal Data** deletes them along with their activity. IP addresses are only ever stored as a salted hash.

## Tests

```bash
vendor/bin/pest tests/Feature/Lead tests/Unit/Lead
```

These cover capture and normalisation, attribution, every spam check, the Livewire form (validation, consent, spam, redirect, locked props), both listeners, the full dispatch-then-outcome flow, settings precedence, retention, CSV escaping, privacy, the capability task and the block's defaults.

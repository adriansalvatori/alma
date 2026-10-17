<?php

/*
|--------------------------------------------------------------------------
| Leads
|--------------------------------------------------------------------------
|
| Precedence for every setting that also appears on Leads → Settings:
|
|     a value set here (normally through .env) wins, otherwise the value saved
|     in wp-admin, otherwise the built-in default.
|
| Leave a variable unset to let editors manage it from wp-admin. Set it to pin
| the value per environment (staging posting to a test webhook, for example);
| the settings screen then shows the field as locked.
|
*/

$list = static fn (?string $value): ?array => $value === null || trim($value) === ''
    ? null
    : array_values(array_filter(array_map('trim', explode(',', $value))));

return [

    // Comma-separated email addresses that receive a notification for each lead.
    'recipients' => $list(env('LEAD_NOTIFICATION_RECIPIENTS')),

    // auto (default): production delivers to every configured destination; other environments only
    // to destinations set in their own .env, because their database is often a copy of production's.
    // always / never override that.
    'delivery' => env('LEAD_DELIVERY'),

    // Every captured lead is POSTed here as JSON (Zapier, Make, n8n, a CRM endpoint...).
    'webhook_url' => env('LEAD_WEBHOOK_URL'),

    // Signs the webhook body (X-Alma-Signature). Without it the webhook is sent unsigned.
    'webhook_secret' => env('LEAD_WEBHOOK_SECRET'),

    // Leads older than this are deleted daily. Never below 30 days, whatever is set.
    'retention_days' => env('LEAD_RETENTION_DAYS'),

    // Store submissions caught by the spam guard (status "spam") instead of dropping them.
    'store_spam' => env('LEAD_STORE_SPAM'),

    // Comma-separated emails or domains (example.com) whose submissions are treated as spam.
    'blocklist' => $list(env('LEAD_BLOCKLIST')),

    // Shown after a successful submission when the form does not set its own message.
    'success_message' => null,

    /*
    |--------------------------------------------------------------------------
    | Spam guard
    |--------------------------------------------------------------------------
    |
    | Not editable in wp-admin: these are tuning knobs, not content.
    |
    */

    'spam' => [
        // A human needs at least this many seconds between the form rendering and submitting.
        'min_seconds' => (int) env('LEAD_MIN_FILL_SECONDS', 3),

        // Submissions allowed per IP address in a ten-minute window.
        'max_attempts' => (int) env('LEAD_RATE_LIMIT', 5),
    ],

    'attribution' => [
        'cookie' => 'alma_attribution',
        'days' => 90,
    ],

];

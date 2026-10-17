{{-- wp-admin → Leads → Settings. Fields pinned by .env are shown read-only with the variable that sets them. --}}
@php
  $locked = fn (string $key) => $settings->lockedByConfig($key);
  $env = [
    'recipients' => 'LEAD_NOTIFICATION_RECIPIENTS',
    'webhook_url' => 'LEAD_WEBHOOK_URL',
    'webhook_secret' => 'LEAD_WEBHOOK_SECRET',
    'retention_days' => 'LEAD_RETENTION_DAYS',
    'store_spam' => 'LEAD_STORE_SPAM',
    'blocklist' => 'LEAD_BLOCKLIST',
  ];
  $notices = [
    'saved' => ['success', __('Settings saved.', 'alma')],
    'webhook-ok' => ['success', sprintf(__('Test webhook delivered (HTTP %d).', 'alma'), $status)],
    'webhook-failed' => ['error', $status ? sprintf(__('The webhook endpoint answered HTTP %d.', 'alma'), $status) : __('The webhook endpoint could not be reached.', 'alma')],
    'webhook-missing' => ['warning', __('Set a webhook URL first.', 'alma')],
  ];
@endphp

<div class="wrap">
  <h1>{{ __('Lead settings', 'alma') }}</h1>

  @if (isset($notices[$notice]))
    <div class="notice notice-{{ $notices[$notice][0] }} is-dismissible"><p>{{ $notices[$notice][1] }}</p></div>
  @endif

  <form method="post" action="{{ admin_url('admin-post.php') }}">
    <input type="hidden" name="action" value="alma_lead_settings">
    @php(wp_nonce_field('alma_lead_settings'))

    <table class="form-table" role="presentation">
      <tr>
        <th scope="row"><label for="alma-recipients">{{ __('Notify', 'alma') }}</label></th>
        <td>
          <textarea id="alma-recipients" name="settings[recipients]" rows="3" class="large-text" @disabled($locked('recipients'))>{{ implode("\n", $settings->recipients()) }}</textarea>
          <p class="description">{{ __('One email address per line. Each new lead is emailed to these addresses.', 'alma') }}</p>
          @includeWhen($locked('recipients'), 'lead::admin.locked', ['variable' => $env['recipients']])
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="alma-webhook-url">{{ __('Webhook URL', 'alma') }}</label></th>
        <td>
          <input id="alma-webhook-url" type="url" name="settings[webhook_url]" value="{{ $settings->webhookUrl() }}" class="regular-text code" placeholder="https://hooks.zapier.com/..." @disabled($locked('webhook_url'))>
          <p class="description">{{ __('Each lead is POSTed here as JSON: Zapier, Make, n8n or your CRM.', 'alma') }}</p>
          @includeWhen($locked('webhook_url'), 'lead::admin.locked', ['variable' => $env['webhook_url']])
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="alma-webhook-secret">{{ __('Webhook secret', 'alma') }}</label></th>
        <td>
          <input id="alma-webhook-secret" type="password" name="settings[webhook_secret]" value="" class="regular-text code" autocomplete="new-password"
            placeholder="{{ $settings->webhookSecret() !== '' ? __('Set (leave blank to keep)', 'alma') : __('Not set', 'alma') }}" @disabled($locked('webhook_secret'))>
          <p class="description">{{ __('Signs every request in the X-Alma-Signature header so the receiver can verify it came from this site.', 'alma') }}</p>
          @includeWhen($locked('webhook_secret'), 'lead::admin.locked', ['variable' => $env['webhook_secret']])
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="alma-retention">{{ __('Keep leads for', 'alma') }}</label></th>
        <td>
          <input id="alma-retention" type="number" min="{{ \App\Domains\Lead\Services\LeadSettings::MINIMUM_RETENTION_DAYS }}" name="settings[retention_days]" value="{{ $settings->retentionDays() }}" class="small-text" @disabled($locked('retention_days'))> {{ __('days', 'alma') }}
          <p class="description">{{ __('Older leads and their activity are deleted daily. The minimum is 30 days.', 'alma') }}</p>
          @includeWhen($locked('retention_days'), 'lead::admin.locked', ['variable' => $env['retention_days']])
        </td>
      </tr>
      <tr>
        <th scope="row">{{ __('Spam', 'alma') }}</th>
        <td>
          <input type="hidden" name="settings[store_spam]" value="0">
          <label><input type="checkbox" name="settings[store_spam]" value="1" @checked($settings->storeSpam()) @disabled($locked('store_spam'))> {{ __('Keep submissions caught by the spam guard (status "Spam") instead of discarding them', 'alma') }}</label>
          @includeWhen($locked('store_spam'), 'lead::admin.locked', ['variable' => $env['store_spam']])
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="alma-blocklist">{{ __('Blocklist', 'alma') }}</label></th>
        <td>
          <textarea id="alma-blocklist" name="settings[blocklist]" rows="3" class="large-text code" @disabled($locked('blocklist'))>{{ implode("\n", $settings->blocklist()) }}</textarea>
          <p class="description">{{ __('Email addresses or domains (example.com), one per line, treated as spam.', 'alma') }}</p>
          @includeWhen($locked('blocklist'), 'lead::admin.locked', ['variable' => $env['blocklist']])
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="alma-success">{{ __('Success message', 'alma') }}</label></th>
        <td>
          <textarea id="alma-success" name="settings[success_message]" rows="2" class="large-text">{{ $settings->successMessage() }}</textarea>
          <p class="description">{{ __('Shown after a submission, unless a form sets its own message or redirect.', 'alma') }}</p>
        </td>
      </tr>
    </table>

    @php(submit_button())
  </form>

  <h2>{{ __('Test the webhook', 'alma') }}</h2>
  <form method="post" action="{{ admin_url('admin-post.php') }}">
    <input type="hidden" name="action" value="alma_lead_webhook_test">
    @php(wp_nonce_field('alma_lead_webhook_test'))
    <p class="description">{{ __('Sends a sample lead (event "lead.test") to the saved webhook URL now.', 'alma') }}</p>
    @php(submit_button(__('Send test webhook', 'alma'), 'secondary', 'submit', false))
  </form>
</div>

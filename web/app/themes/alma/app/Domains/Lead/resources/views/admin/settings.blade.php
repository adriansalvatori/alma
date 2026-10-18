{{-- wp-admin → Leads → Settings. Fields pinned by .env are shown read-only with the variable that sets them. --}}
@php
  $env = [
    'recipients' => 'LEAD_NOTIFICATION_RECIPIENTS',
    'webhook_url' => 'LEAD_WEBHOOK_URL',
    'webhook_secret' => 'LEAD_WEBHOOK_SECRET',
    'retention_days' => 'LEAD_RETENTION_DAYS',
    'store_spam' => 'LEAD_STORE_SPAM',
    'blocklist' => 'LEAD_BLOCKLIST',
  ];
  // The .env variable that pins a setting, or null when wp-admin may change it.
  $locked = fn (string $key) => $settings->lockedByConfig($key) ? $env[$key] : null;
  $notices = [
    'saved' => ['success', __('Settings saved.', 'alma')],
    'webhook-ok' => ['success', sprintf(__('Test webhook delivered (HTTP %d).', 'alma'), $status)],
    'webhook-failed' => ['error', $status ? sprintf(__('The webhook endpoint answered HTTP %d.', 'alma'), $status) : __('The webhook endpoint could not be reached.', 'alma')],
    'webhook-missing' => ['warning', __('Set a webhook URL first.', 'alma')],
  ];
@endphp

<x-admin::page :title="__('Lead settings', 'alma')" :description="__('Who hears about new leads, where they are sent, and how long they are kept.', 'alma')">
  <x-slot:actions>
    <x-admin::button :href="\App\Domains\Lead\Admin\LeadsAdmin::url()" variant="ghost">{{ __('All leads', 'alma') }}</x-admin::button>
  </x-slot:actions>

  @if (isset($notices[$notice]))
    <div class="notice notice-{{ $notices[$notice][0] }} is-dismissible"><p>{{ $notices[$notice][1] }}</p></div>
  @endif

  <div class="alma-stack">
    <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-stack">
      <input type="hidden" name="action" value="alma_lead_settings">
      @php(wp_nonce_field('alma_lead_settings'))

      <x-admin::card :title="__('Delivery', 'alma')" :description="__('Each new lead is emailed to the team and, if set, posted to a webhook.', 'alma')">
        <x-admin::field :label="__('Notify', 'alma')" for="alma-recipients" :hint="__('One email address per line. Each new lead is emailed to these addresses.', 'alma')" :locked="$locked('recipients')">
          <textarea id="alma-recipients" name="settings[recipients]" rows="3" class="alma-input" @disabled($locked('recipients'))>{{ implode("\n", $settings->recipients()) }}</textarea>
        </x-admin::field>

        <x-admin::field :label="__('Webhook URL', 'alma')" for="alma-webhook-url" :hint="__('Each lead is POSTed here as JSON: Zapier, Make, n8n or your CRM.', 'alma')" :locked="$locked('webhook_url')">
          <input id="alma-webhook-url" type="url" name="settings[webhook_url]" value="{{ $settings->webhookUrl() }}" class="alma-input alma-input--code" placeholder="https://hooks.zapier.com/..." @disabled($locked('webhook_url'))>
        </x-admin::field>

        <x-admin::field :label="__('Webhook secret', 'alma')" for="alma-webhook-secret" :hint="__('Signs every request in the X-Alma-Signature header so the receiver can verify it came from this site.', 'alma')" :locked="$locked('webhook_secret')">
          <input id="alma-webhook-secret" type="password" name="settings[webhook_secret]" value="" class="alma-input alma-input--code" autocomplete="new-password"
            placeholder="{{ $settings->webhookSecret() !== '' ? __('Set (leave blank to keep)', 'alma') : __('Not set', 'alma') }}" @disabled($locked('webhook_secret'))>
        </x-admin::field>
      </x-admin::card>

      <x-admin::card :title="__('Retention and spam', 'alma')">
        <x-admin::field :label="__('Keep leads for', 'alma')" for="alma-retention" :hint="__('Older leads and their activity are deleted daily. The minimum is 30 days.', 'alma')" :locked="$locked('retention_days')">
          <input id="alma-retention" type="number" min="{{ \App\Domains\Lead\Services\LeadSettings::MINIMUM_RETENTION_DAYS }}" name="settings[retention_days]" value="{{ $settings->retentionDays() }}" class="alma-input alma-input--short" @disabled($locked('retention_days'))> {{ __('days', 'alma') }}
        </x-admin::field>

        <x-admin::field :label="__('Spam', 'alma')" :locked="$locked('store_spam')">
          <input type="hidden" name="settings[store_spam]" value="0">
          <label class="alma-check"><input type="checkbox" name="settings[store_spam]" value="1" @checked($settings->storeSpam()) @disabled($locked('store_spam'))> {{ __('Keep submissions caught by the spam guard (status "Spam") instead of discarding them', 'alma') }}</label>
        </x-admin::field>

        <x-admin::field :label="__('Blocklist', 'alma')" for="alma-blocklist" :hint="__('Email addresses or domains (example.com), one per line, treated as spam.', 'alma')" :locked="$locked('blocklist')">
          <textarea id="alma-blocklist" name="settings[blocklist]" rows="3" class="alma-input alma-input--code" @disabled($locked('blocklist'))>{{ implode("\n", $settings->blocklist()) }}</textarea>
        </x-admin::field>
      </x-admin::card>

      <x-admin::card :title="__('Form', 'alma')">
        <x-admin::field :label="__('Success message', 'alma')" for="alma-success" :hint="__('Shown after a submission, unless a form sets its own message or redirect.', 'alma')">
          <textarea id="alma-success" name="settings[success_message]" rows="2" class="alma-input">{{ $settings->successMessage() }}</textarea>
        </x-admin::field>

        <x-slot:footer>
          <x-admin::button type="submit" name="submit" variant="primary">{{ __('Save changes', 'alma') }}</x-admin::button>
        </x-slot:footer>
      </x-admin::card>
    </form>

    <x-admin::card :title="__('Test the webhook', 'alma')" :description="__('Sends a sample lead (event “lead.test”) to the saved webhook URL now.', 'alma')">
      <form method="post" action="{{ admin_url('admin-post.php') }}">
        <input type="hidden" name="action" value="alma_lead_webhook_test">
        @php(wp_nonce_field('alma_lead_webhook_test'))
        <x-admin::button type="submit" name="submit">{{ __('Send test webhook', 'alma') }}</x-admin::button>
      </form>
    </x-admin::card>
  </div>
</x-admin::page>

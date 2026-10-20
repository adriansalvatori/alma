{{-- wp-admin → Leads → one lead: who they are, what they sent, where they came from, and the timeline of everything done on their behalf. --}}
@php
  $admin = \App\Domains\Lead\Admin\LeadsAdmin::class;
  $avatar = \App\Domains\Lead\Services\LeadAvatar::class;
  $activity = \App\Domains\Lead\Models\LeadActivity::class;
  $touches = ['first' => __('First touch', 'alma'), 'last' => __('Last touch', 'alma')];
  $clickIds = ['gclid' => __('Google click ID', 'alma'), 'fbclid' => __('Meta click ID', 'alma'), 'msclkid' => __('Microsoft click ID', 'alma')];
  $extraFields = array_filter((array) ($lead->fields ?? []), fn ($value, $field) => ! str_starts_with((string) $field, '_'), ARRAY_FILTER_USE_BOTH);
  $outcomeTone = fn (string $outcome) => match ($outcome) {
    $activity::SUCCEEDED => 'success',
    $activity::FAILED => 'danger',
    $activity::DISPATCHED => 'info',
    default => 'neutral',
  };
  $stringify = fn ($value) => is_scalar($value) || $value === null ? (string) $value : (string) wp_json_encode($value);
  $long = fn (string $value) => mb_strlen($value) > 64 ? mb_substr($value, 0, 32).'…'.mb_substr($value, -8) : $value;
@endphp

<x-admin::page :title="$lead->name !== '' ? $lead->name : $lead->email">
  <x-slot:heading>
    <div style="margin-bottom: 16px;">
      <x-admin::button :href="$admin::url()" size="sm">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        {{ __('Back to all submissions', 'alma') }}
      </x-admin::button>
    </div>

    <x-admin::tabs :tabs="$admin::tabs()" active="leads" />

    <div class="alma-record-header">
      <div class="alma-record-header__who">
        <x-admin::avatar :initials="$avatar::initials($lead->name, $lead->email)" :tint="$avatar::tint($lead->email, $lead->name)" :logo="$avatar::logoUrl($lead->email)" :title="$avatar::domain($lead->email)" :size="48" />
        <div>
          <div class="alma-record-header__name">
            <h1 class="alma-admin-title">{{ $lead->name !== '' ? $lead->name : sprintf(__('Lead #%d', 'alma'), $lead->id) }}</h1>
            <x-admin::badge :tone="$lead->status->tone()" dot>{{ $lead->status->label() }}</x-admin::badge>
            <span class="alma-pill">{{ $lead->form_key }}</span>
          </div>
          <div class="alma-record-header__meta">
            {{ __('Captured:', 'alma') }} <strong>{{ $lead->created_at?->format('F j, Y \a\t g:i:s A') }}</strong> &bull;
            {{ __('ID:', 'alma') }} <code>{{ $lead->uuid }}</code>
          </div>
        </div>
      </div>

      <div class="alma-actions">
        <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-actions">
          <input type="hidden" name="action" value="alma_lead_status">
          <input type="hidden" name="lead" value="{{ $lead->id }}">
          {!! wp_nonce_field('alma_lead_status_'.$lead->id, '_wpnonce', true, false) !!}
          <select id="alma-lead-status" name="status" class="alma-select" aria-label="{{ __('Status', 'alma') }}">
            @foreach ($statuses as $status)
              <option value="{{ $status->value }}" @selected($lead->status === $status)>{{ $status->label() }}</option>
            @endforeach
          </select>
          <x-admin::button type="submit" variant="primary">{{ __('Update status', 'alma') }}</x-admin::button>
        </form>

        <x-admin::button :href="'mailto:'.$lead->email">{{ __('Reply by email', 'alma') }}</x-admin::button>

        <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-inline-form" onsubmit="return confirm(@js(__('Delete this lead and its activity log? This cannot be undone.', 'alma')));">
          <input type="hidden" name="action" value="alma_lead_delete">
          <input type="hidden" name="lead" value="{{ $lead->id }}">
          {!! wp_nonce_field('alma_lead_delete_'.$lead->id, '_wpnonce', true, false) !!}
          <x-admin::button type="submit" variant="danger">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            {{ __('Delete lead', 'alma') }}
          </x-admin::button>
        </form>
      </div>
    </div>
  </x-slot:heading>

  @if ($updated)
    <div class="notice notice-success is-dismissible"><p>{{ __('Lead updated.', 'alma') }}</p></div>
  @endif

  <div class="alma-detail-grid">
    <div>
      <div class="alma-detail-card">
        <h3 class="alma-detail-title">{{ __('Contact & submission', 'alma') }}</h3>
        <table class="alma-kv alma-kv--compact" role="presentation">
          <tr><th scope="row">{{ __('Email', 'alma') }}</th><td><strong><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></strong></td></tr>
          <tr><th scope="row">{{ __('Phone', 'alma') }}</th><td>@if ($lead->phone)<a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>@else — @endif</td></tr>
          <tr><th scope="row">{{ __('Company', 'alma') }}</th><td><strong>{{ $lead->company ?: '—' }}</strong></td></tr>
          <tr><th scope="row">{{ __('Form', 'alma') }}</th><td>{{ $lead->form_key }}</td></tr>
          <tr><th scope="row">{{ __('Consent', 'alma') }}</th><td>{{ $lead->consent ? sprintf(__('Given %s', 'alma'), $lead->consented_at?->toDateTimeString()) : __('Not given', 'alma') }}</td></tr>
          @foreach ($extraFields as $field => $value)
            <tr><th scope="row">{{ $field }}</th><td>{{ $stringify($value) }}</td></tr>
          @endforeach
        </table>
        @if ($lead->message)
          <h3 class="alma-detail-title" style="margin-top: 16px;">{{ __('Message', 'alma') }}</h3>
          <div style="white-space: pre-wrap; font-size: 13px;">{{ $lead->message }}</div>
        @endif
      </div>

      <div class="alma-detail-card">
        <h3 class="alma-detail-title">{{ __('Attribution & UTM data', 'alma') }}</h3>
        <p>{{ __('Where the visitor came from the first time, and on the visit they converted.', 'alma') }}</p>
        <table class="alma-kv alma-kv--compact" role="presentation">
          @foreach ($touches as $key => $label)
            @php($touch = is_array($lead->attribution[$key] ?? null) ? $lead->attribution[$key] : null)
            <tr class="alma-kv__group"><th colspan="2" scope="rowgroup">{{ $label }}</th></tr>
            @if ($touch === null)
              <tr><th scope="row">{{ __('Source', 'alma') }}</th><td class="alma-muted">{{ __('Direct visit (no campaign or referrer recorded)', 'alma') }}</td></tr>
            @else
              <tr><th scope="row">{{ __('Source', 'alma') }}</th><td><strong>{{ $touch['source'] ?? '—' }}</strong></td></tr>
              <tr><th scope="row">{{ __('Medium', 'alma') }}</th><td>{{ $touch['medium'] ?? '—' }}</td></tr>
              <tr><th scope="row">{{ __('Campaign', 'alma') }}</th><td>{{ $touch['campaign'] ?? '—' }}</td></tr>
              @if (! empty($touch['term']) || ! empty($touch['content']))
                <tr><th scope="row">{{ __('Term / content', 'alma') }}</th><td>{{ trim(($touch['term'] ?? '').' '.($touch['content'] ?? '')) }}</td></tr>
              @endif
              @foreach ($clickIds as $clickId => $clickLabel)
                @if (! empty($touch[$clickId]))
                  <tr><th scope="row">{{ $clickLabel }}</th><td><code title="{{ $touch[$clickId] }}">{{ $long($touch[$clickId]) }}</code></td></tr>
                @endif
              @endforeach
              @if (! empty($touch['referrer']))
                <tr><th scope="row">{{ __('Came from', 'alma') }}</th><td style="word-break: break-all; font-size: 11px;">{{ $touch['referrer'] }}</td></tr>
              @endif
              @if (! empty($touch['landing_page']))
                <tr><th scope="row">{{ __('Landing page', 'alma') }}</th><td style="word-break: break-all; font-size: 11px;">{{ $touch['landing_page'] }}</td></tr>
              @endif
              @if (! empty($touch['captured_at']))
                <tr><th scope="row">{{ __('Seen', 'alma') }}</th><td>{{ $touch['captured_at'] }}</td></tr>
              @endif
            @endif
          @endforeach
        </table>
      </div>

      <div class="alma-detail-card">
        <h3 class="alma-detail-title">{{ __('Device', 'alma') }}</h3>
        <table class="alma-kv alma-kv--compact" role="presentation">
          <tr><th scope="row">{{ __('Browser', 'alma') }}</th><td style="word-break: break-all; font-size: 11px;">{{ $lead->user_agent ?: '—' }}</td></tr>
          <tr><th scope="row">{{ __('IP (hashed)', 'alma') }}</th><td>@if ($lead->ip_hash)<code>{{ substr($lead->ip_hash, 0, 16) }}…</code>@else — @endif</td></tr>
        </table>
      </div>
    </div>

    <div class="alma-timeline-col">
      <div class="alma-detail-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f4f4f5; padding-bottom: 10px;">
          <div>
            <h3 class="alma-detail-title" style="margin: 0; border: none; padding: 0;">{{ __('Execution & audit timeline', 'alma') }}</h3>
            <div class="alma-muted" style="font-size: 12px; margin-top: 2px;">{{ __('Each integration records its own outcome. A dispatch without outcomes means the follow-up work never ran.', 'alma') }}</div>
          </div>
          <span class="alma-count-badge">{{ sprintf(_n('%d event', '%d events', $lead->activity->count(), 'alma'), $lead->activity->count()) }}</span>
        </div>

        @if ($lead->activity->isEmpty())
          <p class="alma-faint" style="font-size: 13px;">{{ __('No activity logged yet for this lead.', 'alma') }}</p>
        @else
          <div class="alma-timeline">
            @foreach ($lead->activity as $entry)
              <div class="alma-timeline__item">
                <div class="alma-timeline__dot alma-timeline__dot--{{ $entry->outcome }}"></div>
                <div class="alma-timeline__content">
                  <div class="alma-timeline__meta">
                    <span class="alma-timeline__actor">{{ $entry->actor }}</span>
                    <x-admin::badge :tone="$outcomeTone($entry->outcome)" dot>{{ $entry->outcome }}</x-admin::badge>
                    <span class="alma-timeline__time">{{ $entry->created_at?->format('H:i:s') }} ({{ $entry->created_at?->diffForHumans() }})</span>
                  </div>
                  <div class="alma-timeline__title">{{ $entry->event }}</div>
                  @if (! empty($entry->detail))
                    <details style="margin-top: 6px;">
                      <summary>{{ __('View event payload JSON', 'alma') }}</summary>
                      <div class="alma-json">{{ json_encode($entry->detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</div>
                    </details>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</x-admin::page>

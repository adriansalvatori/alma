{{-- wp-admin → Leads → one lead: what they sent, where they came from, its status and audit log. --}}
@php
  $rows = [
    __('Name', 'alma') => $lead->name,
    __('Email', 'alma') => $lead->email,
    __('Phone', 'alma') => $lead->phone,
    __('Company', 'alma') => $lead->company,
    __('Form', 'alma') => $lead->form_key,
    __('Consent', 'alma') => $lead->consent ? sprintf(__('Given %s', 'alma'), $lead->consented_at?->toDateTimeString()) : __('Not given', 'alma'),
    __('Received', 'alma') => $lead->created_at?->toDateTimeString(),
  ];
  $touches = ['first' => __('First touch', 'alma'), 'last' => __('Last touch', 'alma')];
  $outcomeTone = fn (string $outcome) => match ($outcome) {
    \App\Domains\Lead\Models\LeadActivity::SUCCEEDED => 'success',
    \App\Domains\Lead\Models\LeadActivity::FAILED => 'danger',
    \App\Domains\Lead\Models\LeadActivity::DISPATCHED => 'info',
    default => 'neutral',
  };
@endphp

<x-admin::page :title="$lead->name !== '' ? $lead->name : $lead->email">
  <x-slot:actions>
    <x-admin::badge :tone="$lead->status->tone()">{{ $lead->status->label() }}</x-admin::badge>
    <x-admin::button :href="\App\Domains\Lead\Admin\LeadsAdmin::url()" variant="ghost">{{ __('All leads', 'alma') }}</x-admin::button>
  </x-slot:actions>

  @if ($updated)
    <div class="notice notice-success is-dismissible"><p>{{ __('Lead updated.', 'alma') }}</p></div>
  @endif

  <div class="alma-split">
    <div class="alma-stack">
      <x-admin::card :title="__('Submission', 'alma')">
        <table class="alma-kv" role="presentation">
          @foreach ($rows as $label => $value)
            @if ($value !== null && $value !== '')
              <tr><th scope="row">{{ $label }}</th><td>{{ $value }}</td></tr>
            @endif
          @endforeach
          @foreach ($lead->fields ?? [] as $field => $value)
            @if (! str_starts_with((string) $field, '_'))
              <tr><th scope="row">{{ $field }}</th><td>{{ is_scalar($value) ? $value : wp_json_encode($value) }}</td></tr>
            @endif
          @endforeach
        </table>
        @if ($lead->message)
          <h3>{{ __('Message', 'alma') }}</h3>
          <div style="white-space: pre-wrap">{{ $lead->message }}</div>
        @endif
      </x-admin::card>

      <x-admin::card :title="__('Attribution', 'alma')" :description="__('Where the visitor came from the first time, and on the visit they converted.', 'alma')">
        <div class="alma-grid">
          @foreach ($touches as $key => $label)
            <div>
              <h3>{{ $label }}</h3>
              @if (is_array($lead->attribution[$key] ?? null))
                <table class="alma-kv">
                  @foreach (array_filter($lead->attribution[$key]) as $name => $value)
                    <tr><th scope="row">{{ $name }}</th><td>{{ $value }}</td></tr>
                  @endforeach
                </table>
              @else
                <p>{{ __('Direct visit (no campaign or referrer recorded).', 'alma') }}</p>
              @endif
            </div>
          @endforeach
        </div>
      </x-admin::card>

      <x-admin::card :title="__('Activity', 'alma')" :description="__('Each integration records its own outcome. A dispatch without outcomes means the follow-up work never ran.', 'alma')" flush>
        <x-admin::table>
          <thead><tr><th>{{ __('When', 'alma') }}</th><th>{{ __('Event', 'alma') }}</th><th>{{ __('By', 'alma') }}</th><th>{{ __('Outcome', 'alma') }}</th><th>{{ __('Detail', 'alma') }}</th></tr></thead>
          <tbody>
            @forelse ($lead->activity as $entry)
              <tr>
                <td class="alma-table__nowrap">{{ $entry->created_at?->toDateTimeString() }}</td>
                <td>{{ $entry->event }}</td>
                <td>{{ $entry->actor }}</td>
                <td><x-admin::badge :tone="$outcomeTone($entry->outcome)">{{ $entry->outcome }}</x-admin::badge></td>
                <td><code>{{ $entry->detail ? wp_json_encode($entry->detail) : '' }}</code></td>
              </tr>
            @empty
              <tr><td colspan="5">{{ __('No activity recorded.', 'alma') }}</td></tr>
            @endforelse
          </tbody>
        </x-admin::table>
      </x-admin::card>
    </div>

    <x-admin::card :title="__('Status', 'alma')">
      <form method="post" action="{{ admin_url('admin-post.php') }}">
        <input type="hidden" name="action" value="alma_lead_status">
        <input type="hidden" name="lead" value="{{ $lead->id }}">
        @php(wp_nonce_field('alma_lead_status_'.$lead->id))
        <x-admin::field :label="__('Status', 'alma')" for="alma-lead-status">
          <select id="alma-lead-status" name="status" class="alma-input">
            @foreach ($statuses as $status)
              <option value="{{ $status->value }}" @selected($lead->status === $status)>{{ $status->label() }}</option>
            @endforeach
          </select>
        </x-admin::field>
        <p class="alma-actions" style="margin-top: 16px">
          <x-admin::button type="submit" variant="primary">{{ __('Update status', 'alma') }}</x-admin::button>
          <x-admin::button :href="'mailto:'.$lead->email">{{ __('Reply by email', 'alma') }}</x-admin::button>
        </p>
      </form>
    </x-admin::card>
  </div>
</x-admin::page>

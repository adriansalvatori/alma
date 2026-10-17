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
@endphp

<div class="wrap">
  <h1 class="wp-heading-inline">{{ $lead->name !== '' ? $lead->name : $lead->email }}</h1>
  <a href="{{ \App\Domains\Lead\Admin\LeadsAdmin::url() }}" class="page-title-action">{{ __('All leads', 'alma') }}</a>
  <hr class="wp-header-end">

  @if ($updated)
    <div class="notice notice-success is-dismissible"><p>{{ __('Lead updated.', 'alma') }}</p></div>
  @endif

  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content">
        <div class="postbox">
          <h2 class="hndle">{{ __('Submission', 'alma') }}</h2>
          <div class="inside">
            <table class="form-table" role="presentation">
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
          </div>
        </div>

        <div class="postbox">
          <h2 class="hndle">{{ __('Attribution', 'alma') }}</h2>
          <div class="inside">
            @foreach ($touches as $key => $label)
              <h3>{{ $label }}</h3>
              @if (is_array($lead->attribution[$key] ?? null))
                <table class="widefat striped">
                  @foreach (array_filter($lead->attribution[$key]) as $name => $value)
                    <tr><th scope="row" style="width: 12em">{{ $name }}</th><td>{{ $value }}</td></tr>
                  @endforeach
                </table>
              @else
                <p>{{ __('Direct visit (no campaign or referrer recorded).', 'alma') }}</p>
              @endif
            @endforeach
          </div>
        </div>

        <div class="postbox">
          <h2 class="hndle">{{ __('Activity', 'alma') }}</h2>
          <div class="inside">
            <p class="description">{{ __('Each integration records its own outcome. A dispatch without outcomes means the follow-up work never ran.', 'alma') }}</p>
            <table class="widefat striped">
              <thead><tr><th>{{ __('When', 'alma') }}</th><th>{{ __('Event', 'alma') }}</th><th>{{ __('By', 'alma') }}</th><th>{{ __('Outcome', 'alma') }}</th><th>{{ __('Detail', 'alma') }}</th></tr></thead>
              <tbody>
                @forelse ($lead->activity as $entry)
                  <tr>
                    <td>{{ $entry->created_at?->toDateTimeString() }}</td>
                    <td>{{ $entry->event }}</td>
                    <td>{{ $entry->actor }}</td>
                    <td><strong>{{ $entry->outcome }}</strong></td>
                    <td><code>{{ $entry->detail ? wp_json_encode($entry->detail) : '' }}</code></td>
                  </tr>
                @empty
                  <tr><td colspan="5">{{ __('No activity recorded.', 'alma') }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="postbox-container-1" class="postbox-container">
        <div class="postbox">
          <h2 class="hndle">{{ __('Status', 'alma') }}</h2>
          <div class="inside">
            <form method="post" action="{{ admin_url('admin-post.php') }}">
              <input type="hidden" name="action" value="alma_lead_status">
              <input type="hidden" name="lead" value="{{ $lead->id }}">
              @php(wp_nonce_field('alma_lead_status_'.$lead->id))
              <select name="status" style="width: 100%">
                @foreach ($statuses as $status)
                  <option value="{{ $status->value }}" @selected($lead->status === $status)>{{ $status->label() }}</option>
                @endforeach
              </select>
              <p><button type="submit" class="button button-primary">{{ __('Update status', 'alma') }}</button></p>
            </form>
            <p><a href="mailto:{{ $lead->email }}" class="button">{{ __('Reply by email', 'alma') }}</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

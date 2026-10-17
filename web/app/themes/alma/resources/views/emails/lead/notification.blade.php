{{-- The HTML email the team receives for each new lead. Inline styles only: email clients ignore stylesheets. --}}
@php
  $rows = array_filter([
    __('Name', 'alma') => $lead->name,
    __('Email', 'alma') => $lead->email,
    __('Phone', 'alma') => $lead->phone,
    __('Company', 'alma') => $lead->company,
    __('Form', 'alma') => $lead->form_key,
  ], fn ($value) => $value !== null && $value !== '');
  $source = $lead->attribution['first']['source'] ?? null;
@endphp
<!doctype html>
<html>
<body style="margin:0;padding:24px;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#18181b;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:8px;">
    <tr>
      <td style="padding:24px 24px 8px;">
        <h1 style="margin:0;font-size:18px;">{{ sprintf(__('New lead from %s', 'alma'), $site) }}</h1>
      </td>
    </tr>
    <tr>
      <td style="padding:8px 24px;">
        <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;">
          @foreach ($rows as $label => $value)
            <tr>
              <td style="color:#71717a;width:110px;vertical-align:top;">{{ $label }}</td>
              <td>{{ $value }}</td>
            </tr>
          @endforeach
          @foreach ($lead->fields ?? [] as $field => $value)
            @if (! str_starts_with((string) $field, '_'))
              <tr>
                <td style="color:#71717a;vertical-align:top;">{{ $field }}</td>
                <td>{{ is_scalar($value) ? $value : wp_json_encode($value) }}</td>
              </tr>
            @endif
          @endforeach
          @if ($source)
            <tr>
              <td style="color:#71717a;vertical-align:top;">{{ __('Source', 'alma') }}</td>
              <td>{{ $source }}{{ isset($lead->attribution['first']['campaign']) ? ' / '.$lead->attribution['first']['campaign'] : '' }}</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
    @if ($lead->message)
      <tr>
        <td style="padding:8px 24px;">
          <div style="padding:12px;background:#f4f4f5;border-radius:6px;font-size:14px;white-space:pre-wrap;">{{ $lead->message }}</div>
        </td>
      </tr>
    @endif
    <tr>
      <td style="padding:16px 24px 24px;font-size:13px;">
        <a href="{{ $adminUrl }}" style="color:#4f46e5;">{{ __('Open in wp-admin', 'alma') }}</a>
        &nbsp;·&nbsp; {{ __('Reply to this email to answer the lead directly.', 'alma') }}
      </td>
    </tr>
  </table>
</body>
</html>

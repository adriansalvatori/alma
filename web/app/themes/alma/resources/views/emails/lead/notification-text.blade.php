{{-- Plain-text alternative of the lead notification email. --}}
{!! sprintf(__('New lead from %s', 'alma'), $site) !!}

{!! __('Name', 'alma') !!}: {!! $lead->name !!}
{!! __('Email', 'alma') !!}: {!! $lead->email !!}
@if ($lead->phone)
{!! __('Phone', 'alma') !!}: {!! $lead->phone !!}
@endif
@if ($lead->company)
{!! __('Company', 'alma') !!}: {!! $lead->company !!}
@endif
{!! __('Form', 'alma') !!}: {!! $lead->form_key !!}
@foreach ($lead->fields ?? [] as $field => $value)
@if (! str_starts_with((string) $field, '_'))
{!! $field !!}: {!! is_scalar($value) ? $value : wp_json_encode($value) !!}
@endif
@endforeach
@if ($lead->message)

{!! $lead->message !!}
@endif

{!! $adminUrl !!}

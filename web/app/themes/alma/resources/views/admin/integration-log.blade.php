{{-- Tools → Integration log: the last outbound HTTP calls, newest first, filterable by host. Bodies were redacted when they were stored. --}}
<x-admin::page :title="__('Integration log', 'alma')" :description="__('Outbound HTTP calls made by the site, newest first. Secrets are redacted before they are stored.', 'alma')">
  @if ($missing)
    <x-admin::notice tone="danger"><p>{{ __('The integration log table is missing. Run wp acorn alma:deploy.', 'alma') }}</p></x-admin::notice>
  @else
    <x-admin::card flush>
      <x-slot:actions>
        <form method="get" class="alma-actions">
          <input type="hidden" name="page" value="{{ $slug }}">
          <select name="host" class="alma-input" aria-label="{{ __('Host', 'alma') }}">
            <option value="">{{ __('All hosts', 'alma') }}</option>
            @foreach ($hosts as $option)
              <option value="{{ $option }}" @selected($host === $option)>{{ $option }}</option>
            @endforeach
          </select>
          <x-admin::button type="submit">{{ __('Filter', 'alma') }}</x-admin::button>
        </form>
      </x-slot:actions>

      @if ($calls->isEmpty())
        <x-admin::empty :title="__('No calls recorded', 'alma')" :description="__('Calls to third-party services appear here as the site makes them.', 'alma')" />
      @else
        <x-admin::table>
          <thead>
            <tr>
              <th>{{ __('When', 'alma') }}</th>
              <th>{{ __('Request', 'alma') }}</th>
              <th>{{ __('Status', 'alma') }}</th>
              <th class="alma-table__num">{{ __('Time', 'alma') }}</th>
              <th>{{ __('Response', 'alma') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($calls as $call)
              <tr>
                <td class="alma-table__nowrap">{{ $call->created_at?->toDateTimeString() ?? '' }}</td>
                <td><code>{{ $call->method }}</code> {{ $call->url }}</td>
                <td>
                  @if ($call->error === null && $call->status === null)
                    {{-- A non-blocking call (WordPress' own cron loopback, for one) never waits for an answer. --}}
                    <x-admin::badge>{{ __('sent', 'alma') }}</x-admin::badge>
                  @else
                    <x-admin::badge :tone="$call->succeeded() ? 'success' : 'danger'">{{ $call->error ?? $call->status }}</x-admin::badge>
                  @endif
                </td>
                <td class="alma-table__num">{{ $call->duration_ms !== null ? $call->duration_ms.' ms' : '' }}</td>
                <td>
                  <details>
                    <summary>{{ __('Body', 'alma') }}</summary>
                    <pre class="alma-pre">{{ (string) $call->response_body }}</pre>
                  </details>
                </td>
              </tr>
            @endforeach
          </tbody>
        </x-admin::table>
      @endif
    </x-admin::card>
  @endif
</x-admin::page>

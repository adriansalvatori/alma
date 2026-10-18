{{-- Settings → AI Access: prerequisites, the agent user, its credentials, client configs and recent tool calls. --}}
@php
  $notices = [
    'issued' => ['success', __('Credential issued. Copy it now: it will not be shown again.', 'alma')],
    'issue_failed' => ['error', __('The credential could not be issued. Check that Application Passwords are available and give it a name.', 'alma')],
    'revoked' => ['success', __('Credential revoked. Clients using it stop working immediately.', 'alma')],
    'revoke_failed' => ['error', __('That credential could not be revoked.', 'alma')],
    'reconciled' => ['success', __('Capabilities re-applied from config.', 'alma')],
    'reconcile_failed' => ['error', __('The agent user could not be reconciled. Check the logs.', 'alma')],
    'probed' => ['info', __('Authorization header check finished.', 'alma')],
  ];
  $dot = fn (?bool $ok) => match ($ok) {
    true => 'success',
    false => 'danger',
    null => 'warning',
  };
@endphp

<x-admin::page :title="__('AI Access', 'alma')" :description="__('Let Claude (claude.ai, Claude Desktop, Claude Code) work on this site over MCP. It acts as one WordPress user, and what it may do is decided by that user\'s capabilities.', 'alma')">
  @if (isset($notices[$notice]))
    <div class="notice notice-{{ $notices[$notice][0] }} is-dismissible"><p>{{ $notices[$notice][1] }}</p></div>
  @endif

  @if ($issued)
    <x-admin::notice tone="warning">
      <p><strong>{{ __('New credential for', 'alma') }} “{{ $issued['name'] }}”.</strong> {{ __('Copy it now. It is stored hashed and cannot be shown again.', 'alma') }}</p>
      <p>
        {{ __('Username', 'alma') }}: <code>{{ $issued['login'] }}</code><br>
        {{ __('Password', 'alma') }}: <code style="user-select: all">{{ $issued['password'] }}</code>
      </p>
    </x-admin::notice>
  @endif

  <div class="alma-stack">
    <x-admin::card :title="__('Status', 'alma')" :description="__('What the connection depends on, on this server.', 'alma')">
      <x-slot:actions>
        <form method="post" action="{{ admin_url('admin-post.php') }}">
          <input type="hidden" name="action" value="alma_ai_probe">
          @php(wp_nonce_field('alma_ai_probe'))
          <x-admin::button type="submit" size="sm">{{ __('Check the Authorization header', 'alma') }}</x-admin::button>
        </form>
      </x-slot:actions>

      <table class="alma-kv">
        @foreach ($status as $check)
          <tr>
            <th scope="row"><span class="alma-status-dot alma-status-dot--{{ $dot($check['ok']) }}"></span> {{ $check['label'] }}</th>
            <td>{{ $check['detail'] }}</td>
          </tr>
        @endforeach
      </table>
    </x-admin::card>

    <x-admin::card :title="__('The agent user', 'alma')" :description="__('Set by the AI_AGENT_CAN_* variables in .env and re-applied on every deploy.', 'alma')" flush>
      <x-slot:actions>
        <form method="post" action="{{ admin_url('admin-post.php') }}">
          <input type="hidden" name="action" value="alma_ai_reconcile">
          @php(wp_nonce_field('alma_ai_reconcile'))
          <x-admin::button type="submit" size="sm">{{ __('Re-apply from config', 'alma') }}</x-admin::button>
        </form>
      </x-slot:actions>

      <p class="alma-card__description" style="padding: 0 20px 12px">
        @if ($user)
          <code>{{ $login }}</code> (#{{ $user->ID }}, {{ implode(', ', $user->roles) }})
        @else
          {{ __('Not created yet. Issuing a credential or re-applying capabilities creates it.', 'alma') }}
        @endif
      </p>

      <x-admin::table>
        <thead>
          <tr>
            <th>{{ __('Capability group', 'alma') }}</th>
            <th>{{ __('Config', 'alma') }}</th>
            <th>{{ __('On the user now', 'alma') }}</th>
            <th>{{ __('Capabilities', 'alma') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($grants as $group => $grant)
            <tr>
              <td><code>{{ $group }}</code></td>
              <td><x-admin::badge :tone="$grant['enabled'] ? 'success' : 'neutral'">{{ $grant['enabled'] ? __('granted', 'alma') : __('denied', 'alma') }}</x-admin::badge></td>
              <td><x-admin::badge :tone="$grant['actual'] === $grant['enabled'] ? 'neutral' : 'warning'">{{ $grant['actual'] ? __('yes', 'alma') : __('no', 'alma') }}</x-admin::badge></td>
              <td><code>{{ implode(', ', $grant['capabilities']) }}</code></td>
            </tr>
          @endforeach
        </tbody>
      </x-admin::table>
    </x-admin::card>

    <x-admin::card :title="__('Credentials', 'alma')" :description="__('One per person and client, so each can be revoked on its own.', 'alma')" flush>
      @if ($credentials)
        <x-admin::table>
          <thead>
            <tr>
              <th>{{ __('Name', 'alma') }}</th>
              <th>{{ __('Created', 'alma') }}</th>
              <th>{{ __('Last used', 'alma') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($credentials as $credential)
              <tr>
                <td>{{ $credential['name'] }}</td>
                <td>{{ $credential['created'] ? wp_date('Y-m-d H:i', $credential['created']) : '—' }}</td>
                <td>{{ $credential['last_used'] ? wp_date('Y-m-d H:i', $credential['last_used']) : __('never', 'alma') }}</td>
                <td class="alma-table__num">
                  <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-inline-form" onsubmit="return confirm('{{ esc_js(__('Revoke this credential?', 'alma')) }}')">
                    <input type="hidden" name="action" value="alma_ai_revoke">
                    <input type="hidden" name="uuid" value="{{ $credential['uuid'] }}">
                    @php(wp_nonce_field('alma_ai_revoke'))
                    <x-admin::button type="submit" variant="danger" size="sm">{{ __('Revoke', 'alma') }}</x-admin::button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </x-admin::table>
      @else
        <x-admin::empty :title="__('No credentials yet', 'alma')" :description="__('Issue one for each person and client that connects.', 'alma')" />
      @endif

      <x-slot:footer>
        <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-actions" style="width: 100%">
          <input type="hidden" name="action" value="alma_ai_issue">
          @php(wp_nonce_field('alma_ai_issue'))
          <input type="text" name="label" class="alma-input" style="max-width: 360px" placeholder="{{ __('Whose is it? e.g. Ada, Claude Desktop', 'alma') }}" required>
          <x-admin::button type="submit" variant="primary">{{ __('Issue credential', 'alma') }}</x-admin::button>
        </form>
      </x-slot:footer>
    </x-admin::card>

    <x-admin::card :title="__('Connect a client', 'alma')">
      <p>{{ __('MCP endpoint', 'alma') }}: <code>{{ $endpoint }}</code></p>
      <h3>{{ __('Claude Desktop and Claude Code', 'alma') }}</h3>
      <p>{{ __('Add this server to claude_desktop_config.json, or to .mcp.json in a project (requires Node.js on that computer).', 'alma') }}</p>
      <textarea class="alma-input alma-input--code" rows="14" readonly>{{ $desktopConfig }}</textarea>
      <h3>{{ __('claude.ai', 'alma') }}</h3>
      <p>{{ __('Add a custom connector with the endpoint above. Connectors authenticate with a Basic Authorization header (username:password, base64-encoded); where your organisation does not allow request headers, use Claude Desktop instead. See docs/ai.md.', 'alma') }}</p>
    </x-admin::card>

    <x-admin::card :title="__('Recent tool calls', 'alma')" flush>
      @if ($calls)
        <x-admin::table>
          <thead>
            <tr>
              <th>{{ __('When (UTC)', 'alma') }}</th>
              <th>{{ __('User', 'alma') }}</th>
              <th>{{ __('Ability', 'alma') }}</th>
              <th>{{ __('Outcome', 'alma') }}</th>
              <th class="alma-table__num">{{ __('ms', 'alma') }}</th>
              <th>{{ __('Error', 'alma') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($calls as $call)
              <tr>
                <td class="alma-table__nowrap">{{ $call['when'] }}</td>
                <td>{{ $call['user'] }}</td>
                <td><code>{{ $call['ability'] }}</code></td>
                <td><x-admin::badge :tone="$call['outcome'] === 'succeeded' ? 'success' : ($call['outcome'] === 'failed' ? 'danger' : 'neutral')">{{ $call['outcome'] }}</x-admin::badge></td>
                <td class="alma-table__num">{{ $call['duration_ms'] ?? '—' }}</td>
                <td>{{ $call['error'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </x-admin::table>
      @else
        <x-admin::empty :title="__('No calls yet', 'alma')" :description="__('Tool calls from connected clients are listed here.', 'alma')" />
      @endif
    </x-admin::card>
  </div>
</x-admin::page>

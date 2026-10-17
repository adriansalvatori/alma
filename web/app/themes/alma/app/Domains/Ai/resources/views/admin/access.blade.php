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
  $badge = fn (?bool $ok) => match ($ok) {
    true => ['dashicons-yes-alt', '#00a32a'],
    false => ['dashicons-warning', '#d63638'],
    null => ['dashicons-marker', '#996800'],
  };
@endphp

<div class="wrap">
  <h1>{{ __('AI Access', 'alma') }}</h1>
  <p>{{ __('Let Claude (claude.ai, Claude Desktop, Claude Code) work on this site over MCP. It acts as one WordPress user, and what it may do is decided by that user\'s capabilities.', 'alma') }}</p>

  @if (isset($notices[$notice]))
    <div class="notice notice-{{ $notices[$notice][0] }} is-dismissible"><p>{{ $notices[$notice][1] }}</p></div>
  @endif

  @if ($issued)
    <div class="notice notice-warning">
      <p><strong>{{ __('New credential for', 'alma') }} “{{ $issued['name'] }}”.</strong> {{ __('Copy it now. It is stored hashed and cannot be shown again.', 'alma') }}</p>
      <p>
        {{ __('Username', 'alma') }}: <code>{{ $issued['login'] }}</code><br>
        {{ __('Password', 'alma') }}: <code style="user-select: all">{{ $issued['password'] }}</code>
      </p>
    </div>
  @endif

  <h2>{{ __('Status', 'alma') }}</h2>
  <table class="widefat striped" style="max-width: 900px">
    <tbody>
      @foreach ($status as $check)
        @php([$icon, $color] = $badge($check['ok']))
        <tr>
          <td style="width: 28px"><span class="dashicons {{ $icon }}" style="color: {{ $color }}"></span></td>
          <th scope="row" style="width: 220px">{{ $check['label'] }}</th>
          <td>{{ $check['detail'] }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <form method="post" action="{{ admin_url('admin-post.php') }}" style="margin-top: 8px">
    <input type="hidden" name="action" value="alma_ai_probe">
    @php(wp_nonce_field('alma_ai_probe'))
    <button class="button">{{ __('Check the Authorization header', 'alma') }}</button>
  </form>

  <h2>{{ __('The agent user', 'alma') }}</h2>
  <p>
    @if ($user)
      <code>{{ $login }}</code> (#{{ $user->ID }}, {{ implode(', ', $user->roles) }})
    @else
      {{ __('Not created yet. Issuing a credential or re-applying capabilities creates it.', 'alma') }}
    @endif
  </p>
  <table class="widefat striped" style="max-width: 900px">
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
          <td>{{ $grant['enabled'] ? __('granted', 'alma') : __('denied', 'alma') }}</td>
          <td>{{ $grant['actual'] ? __('yes', 'alma') : __('no', 'alma') }}</td>
          <td><code>{{ implode(', ', $grant['capabilities']) }}</code></td>
        </tr>
      @endforeach
    </tbody>
  </table>
  <p class="description">{{ __('Set by the AI_AGENT_CAN_* variables in .env and re-applied on every deploy.', 'alma') }}</p>
  <form method="post" action="{{ admin_url('admin-post.php') }}">
    <input type="hidden" name="action" value="alma_ai_reconcile">
    @php(wp_nonce_field('alma_ai_reconcile'))
    <button class="button">{{ __('Re-apply from config', 'alma') }}</button>
  </form>

  <h2>{{ __('Credentials', 'alma') }}</h2>
  <p>{{ __('One per person and client, so each can be revoked on its own.', 'alma') }}</p>
  @if ($credentials)
    <table class="widefat striped" style="max-width: 900px">
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
            <td>
              <form method="post" action="{{ admin_url('admin-post.php') }}" onsubmit="return confirm('{{ esc_js(__('Revoke this credential?', 'alma')) }}')">
                <input type="hidden" name="action" value="alma_ai_revoke">
                <input type="hidden" name="uuid" value="{{ $credential['uuid'] }}">
                @php(wp_nonce_field('alma_ai_revoke'))
                <button class="button-link-delete">{{ __('Revoke', 'alma') }}</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
  <form method="post" action="{{ admin_url('admin-post.php') }}" style="margin-top: 8px">
    <input type="hidden" name="action" value="alma_ai_issue">
    @php(wp_nonce_field('alma_ai_issue'))
    <input type="text" name="label" class="regular-text" placeholder="{{ __('Whose is it? e.g. Ada, Claude Desktop', 'alma') }}" required>
    <button class="button button-primary">{{ __('Issue credential', 'alma') }}</button>
  </form>

  <h2>{{ __('Connect a client', 'alma') }}</h2>
  <p>{{ __('MCP endpoint', 'alma') }}: <code>{{ $endpoint }}</code></p>
  <h3>{{ __('Claude Desktop and Claude Code', 'alma') }}</h3>
  <p>{{ __('Add this server to claude_desktop_config.json, or to .mcp.json in a project (requires Node.js on that computer).', 'alma') }}</p>
  <textarea class="large-text code" rows="14" readonly>{{ $desktopConfig }}</textarea>
  <h3>{{ __('claude.ai', 'alma') }}</h3>
  <p>{{ __('Add a custom connector with the endpoint above. Connectors authenticate with a Basic Authorization header (username:password, base64-encoded); where your organisation does not allow request headers, use Claude Desktop instead. See docs/ai.md.', 'alma') }}</p>

  <h2>{{ __('Recent tool calls', 'alma') }}</h2>
  @if ($calls)
    <table class="widefat striped">
      <thead>
        <tr>
          <th>{{ __('When (UTC)', 'alma') }}</th>
          <th>{{ __('User', 'alma') }}</th>
          <th>{{ __('Ability', 'alma') }}</th>
          <th>{{ __('Outcome', 'alma') }}</th>
          <th>{{ __('ms', 'alma') }}</th>
          <th>{{ __('Error', 'alma') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($calls as $call)
          <tr>
            <td>{{ $call['when'] }}</td>
            <td>{{ $call['user'] }}</td>
            <td><code>{{ $call['ability'] }}</code></td>
            <td>{{ $call['outcome'] }}</td>
            <td>{{ $call['duration_ms'] ?? '—' }}</td>
            <td>{{ $call['error'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @else
    <p>{{ __('No calls yet.', 'alma') }}</p>
  @endif
</div>

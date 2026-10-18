{{-- Tools → Redirects: add and remove database redirects; the ones in config/redirects.php are listed read-only. --}}
<x-admin::page :title="__('Redirects', 'alma')" :description="__('Send an old or campaign URL somewhere else without a deploy. Redirects that live in code (config/redirects.php) are listed too, read-only.', 'alma')">
  @foreach ($notices as [$type, $message])
    <div class="notice notice-{{ $type }} {{ $type === 'success' ? 'is-dismissible' : '' }}"><p>{{ $message }}</p></div>
  @endforeach

  @if ($missing)
    <x-admin::notice tone="danger"><p>{{ __('The redirects table is missing. Run wp acorn alma:deploy.', 'alma') }}</p></x-admin::notice>
  @endif

  <div class="alma-stack">
    <x-admin::card :title="__('Add a redirect', 'alma')">
      <form method="post" action="{{ admin_url('admin-post.php') }}" id="alma-redirect-create">
        @php(wp_nonce_field('alma_redirect_create'))
        <input type="hidden" name="action" value="alma_redirect_create">
        <div class="alma-form-grid">
          <x-admin::field :label="__('From path', 'alma')" for="from_path">
            <input name="from_path" id="from_path" class="alma-input alma-input--code" placeholder="/old-page" required>
          </x-admin::field>
          <x-admin::field :label="__('To', 'alma')" for="to_url">
            <input name="to_url" id="to_url" class="alma-input alma-input--code" placeholder="/new-page/ or https://example.com/" required>
          </x-admin::field>
          <x-admin::field :label="__('Type', 'alma')" for="status_code">
            <select name="status_code" id="status_code" class="alma-input">
              <option value="301">301 {{ __('Permanent', 'alma') }}</option>
              <option value="302">302 {{ __('Temporary', 'alma') }}</option>
            </select>
          </x-admin::field>
          <x-admin::field :label="__('Note', 'alma')" for="note">
            <input name="note" id="note" class="alma-input" maxlength="255">
          </x-admin::field>
        </div>
      </form>

      <x-slot:footer>
        <x-admin::button type="submit" form="alma-redirect-create" name="submit" variant="primary">{{ __('Add redirect', 'alma') }}</x-admin::button>
      </x-slot:footer>
    </x-admin::card>

    <x-admin::card :title="__('Redirects', 'alma')" flush>
      @if ($rows->isEmpty() && $code === [])
        <x-admin::empty :title="__('No redirects yet', 'alma')" :description="__('Add one above, or list permanent ones in config/redirects.php.', 'alma')" />
      @else
        <x-admin::table>
          <thead>
            <tr>
              <th>{{ __('From', 'alma') }}</th>
              <th>{{ __('To', 'alma') }}</th>
              <th>{{ __('Type', 'alma') }}</th>
              <th class="alma-table__num">{{ __('Hits', 'alma') }}</th>
              <th>{{ __('Note', 'alma') }}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr>
                <td><code>{{ $row->from_path }}</code></td>
                <td>{{ $row->to_url }}</td>
                <td><x-admin::badge>{{ $row->status_code }}</x-admin::badge></td>
                <td class="alma-table__num">{{ $row->hits }}</td>
                <td>{{ $row->note }}</td>
                <td class="alma-table__num">
                  <form method="post" action="{{ admin_url('admin-post.php') }}" class="alma-inline-form" onsubmit="return confirm('{{ esc_js(__('Delete this redirect?', 'alma')) }}')">
                    @php(wp_nonce_field('alma_redirect_delete'))
                    <input type="hidden" name="action" value="alma_redirect_delete">
                    <input type="hidden" name="id" value="{{ $row->id }}">
                    <x-admin::button type="submit" variant="danger" size="sm">{{ __('Delete', 'alma') }}</x-admin::button>
                  </form>
                </td>
              </tr>
            @endforeach
            @foreach ($code as $path => $redirect)
              <tr>
                <td><code>{{ $path }}</code></td>
                <td>{{ $redirect['to'] }}</td>
                <td><x-admin::badge>{{ $redirect['status'] }}</x-admin::badge></td>
                <td class="alma-table__num">&ndash;</td>
                <td><x-admin::badge tone="info">{{ __('In code (config/redirects.php)', 'alma') }}</x-admin::badge></td>
                <td></td>
              </tr>
            @endforeach
          </tbody>
        </x-admin::table>
      @endif
    </x-admin::card>
  </div>
</x-admin::page>

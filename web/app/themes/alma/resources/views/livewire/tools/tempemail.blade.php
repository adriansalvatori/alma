<div class="card">
    <header class="card-header">
        <p class="card-header-title">Temporary Email Result</p>
    </header>
    <div class="card-content">
        @if ($data['action'] === 'generate')
            <p><strong>Email:</strong> {{ $data['email'] ?? 'N/A' }}</p>
            <p><strong>Token:</strong> {{ $data['token'] ?? 'N/A' }}</p>
            <p class="is-size-7 has-text-grey-light">Inbox expires after 1 hour.</p>
        @elseif ($data['action'] === 'fetch')
            @if (!empty($data['emails']))
                <table class="table is-bordered is-fullwidth">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['emails'] as $email)
                            <tr>
                                <td>{{ htmlspecialchars($email['from'] ?? 'Unknown') }}</td>
                                <td>{{ htmlspecialchars($email['subject'] ?? 'No subject') }}</td>
                                <td>{{ htmlspecialchars($email['date'] ?? 'N/A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No emails found.</p>
            @endif
        @elseif ($data['action'] === 'send')
            <p>Email sent to <strong>{{ $data['recipient'] ?? 'N/A' }}</strong> with subject <strong>{{ $data['subject'] ?? 'N/A' }}</strong>.</p>
        @else
            <p>Unknown temporary email action.</p>
        @endif
    </div>
</div>
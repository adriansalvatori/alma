<div class="card">
    <header class="card-header">
        <p class="card-header-title">Weather in {{ $data['city'] ?? 'Unknown' }}</p>
    </header>
    <div class="card-content">
        <p><strong>Temperature:</strong> {{ $data['temperature'] ?? 'N/A' }}°C</p>
        <p><strong>Condition:</strong> {{ $data['condition'] ?? 'N/A' }}</p>
        <p><strong>Wind:</strong> {{ $data['windspeed'] ?? 'N/A' }} m/s from {{ $data['winddirection'] ?? 'N/A' }}°</p>
        <p><strong>Time:</strong> {{ $data['time'] ?? 'N/A' }}</p>
    </div>
</div>
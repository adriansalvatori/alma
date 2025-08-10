<div class="card">
    <header class="card-header">
        <p class="card-header-title">{{ $data['dataset_label'] ?? 'Chart' }}</p>
    </header>
    <div class="card-content">
        <img src="{{ $data['image_url'] }}" alt="Chart of {{ $data['dataset_label'] ?? 'Data' }}" style="width: {{ $data['size'] }}px; height: {{ $data['size'] }}px;">
    </div>
</div>
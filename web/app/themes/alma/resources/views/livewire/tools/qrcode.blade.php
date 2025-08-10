<div class="card">
    <header class="card-header">
        <p class="card-header-title">QR Code for {{ $data['url'] ?? 'URL' }}</p>
    </header>
    <div class="card-content">
        <img src="{{ $data['image_url'] }}" alt="QR code for {{ $data['url'] ?? 'URL' }}" style="width: {{ $data['size'] }}px; height: {{ $data['size'] }}px;">
    </div>
</div>
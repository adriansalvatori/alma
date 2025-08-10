<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;

class QRCodeTool
{
    public function definition()
    {
        return Tool::as('qrcode')
            ->for('Generate a QR code image tag for a given URL')
            ->withStringParameter('url', 'The URL to encode in the QR code')
            ->withStringParameter('size', 'The size of the QR code image (e.g., 100x100)', '100x100')
            ->using(function (string $url, string $size = '100x100') {
                // Validate URL
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    Log::debug("Invalid URL provided for QR code: $url");
                    return json_encode(['type' => 'error', 'data' => ['message' => 'Invalid URL provided.'], 'view' => 'livewire.tools.error']);
                }

                // Validate size format (e.g., 100x100)
                if (!preg_match('/^\d+x\d+$/', $size)) {
                    Log::debug("Invalid size format for QR code: $size");
                    return json_encode(['type' => 'error', 'data' => ['message' => 'Invalid size format. Use format like "100x100".'], 'view' => 'livewire.tools.error']);
                }

                // Construct QR code API URL
                $apiUrl = "http://api.qrserver.com/v1/create-qr-code/";
                $queryParams = [
                    'data' => urlencode($url),
                    'size' => $size,
                ];

                $qrCodeUrl = $apiUrl . '?' . http_build_query($queryParams);
                Log::debug("Generated QR code URL for $url with size $size: $qrCodeUrl");

                // Return structured JSON with view
                return json_encode([
                    'type' => 'qrcode',
                    'data' => [
                        'url' => $url,
                        'image_url' => $qrCodeUrl,
                        'size' => $size,
                    ],
                    'view' => 'livewire.tools.qrcode',
                ]);
            });
    }
}
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
                    return "Error: Invalid URL provided.";
                }

                // Validate size format (e.g., 100x100)
                if (!preg_match('/^\d+x\d+$/', $size)) {
                    Log::debug("Invalid size format for QR code: $size");
                    return "Error: Invalid size format. Use format like '100x100'.";
                }

                // Construct QR code API URL
                $apiUrl = "http://api.qrserver.com/v1/create-qr-code/";
                $queryParams = [
                    'data' => urlencode($url),
                    'size' => $size,
                ];

                $qrCodeUrl = $apiUrl . '?' . http_build_query($queryParams);
                Log::debug("Generated QR code URL for $url with size $size: $qrCodeUrl");

                // Return HTML img tag with QR code
                $imgTag = "<img src=\"$qrCodeUrl\" alt=\"QR code for $url\" style=\"width: {$size}px; height: {$size}px;\">";
                return $imgTag;
            });
    }
}
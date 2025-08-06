<?php

namespace App\Tools;

use Illuminate\Support\Facades\Log;
use Prism\Prism\Facades\Tool;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class FileSharingTool
{
    public function definition()
    {
        return Tool::as('filesharing')
            ->for('Upload a file to file.io and get a shareable link')
            ->withStringParameter('file_path', 'The path to the file to upload')
            ->using(function (string $file_path) {
                // Validate file existence and size
                if (!file_exists($file_path)) {
                    Log::debug("File not found: $file_path");
                    return "Error: File not found at $file_path.";
                }

                if (filesize($file_path) > 4 * 1024 * 1024 * 1024) { // 4GB limit for free tier
                    Log::debug("File too large: $file_path, size: " . filesize($file_path));
                    return "Error: File size exceeds 5GB limit.";
                }

                // Prepare file for upload
                $file = new UploadedFile($file_path, basename($file_path));

                // Upload file to file.io
                $response = Http::attach(
                    'file', file_get_contents($file_path), basename($file_path)
                )->post('https://file.io');

                $data = $response->json();
                Log::debug("File.io upload response for $file_path: " . json_encode($data));

                if ($response->successful() && isset($data['link'])) {
                    $downloadLink = $data['link'];
                    Log::debug("File uploaded successfully: $file_path, link: $downloadLink");
                    return "<a href=\"$downloadLink\" target=\"_blank\">Download your file: $downloadLink</a>";
                }

                Log::debug("Error uploading file to file.io: " . ($response->failed() ? $response->status() : 'No link returned'));
                return "Error uploading file: " . ($response->failed() ? $response->status() : 'No link returned');
            });
    }
}
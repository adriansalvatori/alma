<?php

namespace App\Domains\Content\Hooks;

/**
 * Smaller images from the media library, with no build step.
 *
 * When the server's image library can write WebP (GD or Imagick, both common on shared hosting),
 * WordPress generates the resized copies of a JPEG or PNG upload as WebP. The original stays as
 * uploaded. Turn off with content.webp_uploads = false.
 */
class MediaDefaults
{
    public function register(): void
    {
        if (! config('content.webp_uploads', true)) {
            return;
        }

        add_filter('image_editor_output_format', [$this, 'webp']);
    }

    /**
     * @param  array<string, string>  $formats
     * @return array<string, string>
     */
    public function webp(array $formats): array
    {
        return $formats + [
            'image/jpeg' => 'image/webp',
            'image/png' => 'image/webp',
        ];
    }
}

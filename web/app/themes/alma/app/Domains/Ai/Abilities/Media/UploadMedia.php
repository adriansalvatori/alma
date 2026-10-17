<?php

namespace App\Domains\Ai\Abilities\Media;

use App\Domains\Ai\Abilities\Ability;
use WP_Error;

/**
 * Add a file to the media library, from a URL or base64 data.
 *
 * URLs are fetched with download_url(), which goes through wp_safe_remote_get(): it refuses
 * private and loopback addresses, so the ability cannot be used to probe the server's network.
 * The type is checked against the file's real contents, not its name, and limited to the
 * allowlist in config/ai.php (no SVG: it can carry script and would be served from this origin).
 */
class UploadMedia extends Ability
{
    public function name(): string
    {
        return 'alma/upload-media';
    }

    public function label(): string
    {
        return __('Upload media', 'alma');
    }

    public function description(): string
    {
        return __('Add an image or PDF to the media library from a public URL or base64 data, with alt text. Returns the attachment id and URL.', 'alma');
    }

    public function category(): string
    {
        return 'alma-media';
    }

    protected function inputProperties(): array
    {
        return [
            'url' => ['type' => 'string', 'description' => 'A public http(s) URL to fetch.'],
            'data' => ['type' => 'string', 'description' => 'Base64-encoded file contents (instead of url).'],
            'filename' => ['type' => 'string', 'description' => 'File name with extension. Required with data.'],
            'title' => ['type' => 'string'],
            'alt' => ['type' => 'string', 'description' => 'Alt text for images. Describe the image; never leave it empty for content images.'],
        ];
    }

    public function outputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'url' => ['type' => 'string'],
                'mime' => ['type' => 'string'],
                'bytes' => ['type' => 'integer'],
            ],
            'required' => ['id', 'url'],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('upload_files');
    }

    public function execute(array $input): array|WP_Error
    {
        $this->loadMediaFunctions();

        $file = $this->fetch($input);

        if ($file instanceof WP_Error) {
            return $file;
        }

        [$tmp, $filename] = $file;

        try {
            return $this->store($tmp, $filename, $input);
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
    }

    /**
     * Download or decode the file into a temporary path.
     *
     * @param  array<string, mixed>  $input
     * @return array{string, string}|WP_Error
     */
    private function fetch(array $input): array|WP_Error
    {
        $url = is_string($input['url'] ?? null) ? trim($input['url']) : '';
        $data = is_string($input['data'] ?? null) ? $input['data'] : '';
        $max = (int) config('ai.media.max_bytes');

        if (($url === '') === ($data === '')) {
            return new WP_Error('alma_ai_media_source', __('Pass either url or data, not both.', 'alma'));
        }

        if ($url !== '') {
            if (! preg_match('#^https?://#i', $url)) {
                return new WP_Error('alma_ai_media_url', __('Only http(s) URLs can be fetched.', 'alma'));
            }

            $tmp = download_url($url, 30);

            if ($tmp instanceof WP_Error) {
                return $tmp;
            }

            $filename = is_string($input['filename'] ?? null) && $input['filename'] !== ''
                ? $input['filename']
                : basename((string) parse_url($url, PHP_URL_PATH));
        } else {
            if (strlen($data) > (int) ceil($max * 4 / 3) + 4) {
                return $this->tooLarge($max);
            }

            $bytes = base64_decode(preg_replace('#^data:[^;]+;base64,#', '', $data), true);

            if ($bytes === false || $bytes === '') {
                return new WP_Error('alma_ai_media_data', __('data is not valid base64.', 'alma'));
            }

            if (! is_string($input['filename'] ?? null) || $input['filename'] === '') {
                return new WP_Error('alma_ai_media_filename', __('filename (with extension) is required with data.', 'alma'));
            }

            $filename = $input['filename'];
            $tmp = wp_tempnam($filename);
            file_put_contents($tmp, $bytes);
        }

        if (filesize($tmp) > $max) {
            @unlink($tmp);

            return $this->tooLarge($max);
        }

        return [$tmp, sanitize_file_name($filename)];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>|WP_Error
     */
    private function store(string $tmp, string $filename, array $input): array|WP_Error
    {
        $allowed = (array) config('ai.media.mime_types', []);
        $type = wp_check_filetype_and_ext($tmp, $filename, $allowed);

        if (empty($type['type']) || ! in_array($type['type'], $allowed, true)) {
            return new WP_Error('alma_ai_media_type', sprintf(
                /* translators: %s: allowed file types */
                __('That file type is not allowed. Allowed: %s.', 'alma'),
                implode(', ', array_keys($allowed)),
            ));
        }

        $bytes = (int) filesize($tmp);
        $title = is_string($input['title'] ?? null) ? sanitize_text_field($input['title']) : null;

        $id = media_handle_sideload(
            ['name' => $type['proper_filename'] ?: $filename, 'tmp_name' => $tmp],
            0,
            $title,
        );

        if ($id instanceof WP_Error) {
            return $id;
        }

        if (is_string($input['alt'] ?? null) && trim($input['alt']) !== '') {
            update_post_meta((int) $id, '_wp_attachment_image_alt', sanitize_text_field($input['alt']));
        }

        return [
            'id' => (int) $id,
            'url' => (string) wp_get_attachment_url((int) $id),
            'mime' => (string) $type['type'],
            'bytes' => $bytes,
        ];
    }

    private function tooLarge(int $max): WP_Error
    {
        return new WP_Error('alma_ai_media_size', sprintf(
            /* translators: %s: maximum size in megabytes */
            __('The file is larger than the %s MB limit.', 'alma'),
            round($max / 1048576, 1),
        ));
    }

    /**
     * download_url() and media_handle_sideload() live in wp-admin includes, which REST and MCP
     * requests do not load.
     */
    private function loadMediaFunctions(): void
    {
        foreach (['file.php' => 'download_url', 'media.php' => 'media_handle_sideload', 'image.php' => 'wp_generate_attachment_metadata'] as $file => $function) {
            if (! function_exists($function) && is_file(ABSPATH.'wp-admin/includes/'.$file)) {
                require_once ABSPATH.'wp-admin/includes/'.$file;
            }
        }
    }
}

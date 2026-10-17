<?php

namespace App\Domains\Ai\Abilities\Media;

use App\Domains\Ai\Abilities\Ability;
use WP_Error;
use WP_Post;

class ListMedia extends Ability
{
    public function name(): string
    {
        return 'alma/list-media';
    }

    public function label(): string
    {
        return __('List media', 'alma');
    }

    public function description(): string
    {
        return __('Search the media library, newest first. Filter by text and by type (image, application/pdf, ...). Returns ids and URLs to reuse in pages and posts.', 'alma');
    }

    public function category(): string
    {
        return 'alma-media';
    }

    public function readOnly(): bool
    {
        return true;
    }

    public function idempotent(): bool
    {
        return true;
    }

    protected function inputProperties(): array
    {
        return [
            'search' => ['type' => 'string'],
            'type' => ['type' => 'string', 'description' => 'A mime type or its first half, e.g. "image" or "application/pdf".'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('upload_files');
    }

    public function execute(array $input): array
    {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => is_string($input['type'] ?? null) ? $input['type'] : '',
            's' => is_string($input['search'] ?? null) ? $input['search'] : '',
            'numberposts' => min(100, max(1, (int) ($input['limit'] ?? 20))),
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return array_map(fn (WP_Post $attachment) => [
            'id' => $attachment->ID,
            'title' => $attachment->post_title,
            'url' => (string) wp_get_attachment_url($attachment->ID),
            'mime' => (string) get_post_mime_type($attachment),
            'alt' => (string) get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'date' => $attachment->post_date,
        ], $attachments);
    }
}

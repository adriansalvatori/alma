<?php

namespace App\Domains\Ai\Abilities\Posts;

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Ai\Support\MarkdownBlocks;
use WP_Error;

/**
 * Draft a blog post from text or light Markdown. A draft by default; "pending" sends it for
 * review, and "publish" only works for a user allowed to publish (the agent is not, unless
 * AI_AGENT_CAN_PUBLISH is on).
 */
class CreateDraftPost extends Ability
{
    public function __construct(private readonly MarkdownBlocks $markdown) {}

    public function name(): string
    {
        return 'alma/create-draft-post';
    }

    public function label(): string
    {
        return __('Create draft post', 'alma');
    }

    public function description(): string
    {
        return __('Create a blog post from plain text or light Markdown (headings, lists, bold, italic, links), saved as a draft unless asked otherwise. Returns the edit link for a human to review.', 'alma');
    }

    public function category(): string
    {
        return 'alma-content';
    }

    protected function inputProperties(): array
    {
        return [
            'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200],
            'content' => ['type' => 'string', 'description' => 'Plain text or light Markdown.'],
            'excerpt' => ['type' => 'string', 'maxLength' => 500],
            'status' => ['type' => 'string', 'enum' => ['draft', 'pending', 'publish'], 'default' => 'draft'],
        ];
    }

    protected function requiredInput(): array
    {
        return ['title'];
    }

    public function outputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'status' => ['type' => 'string'],
                'url' => ['type' => 'string'],
                'edit_url' => ['type' => 'string'],
            ],
            'required' => ['id', 'status'],
        ];
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can('edit_posts');
    }

    public function execute(array $input): array|WP_Error
    {
        $status = in_array($input['status'] ?? 'draft', ['draft', 'pending', 'publish'], true) ? $input['status'] ?? 'draft' : 'draft';

        if ($status === 'publish' && ! current_user_can('publish_posts')) {
            return new WP_Error('alma_ai_cannot_publish', __('This user may not publish. Save it as a draft or pending review instead.', 'alma'));
        }

        // wp_insert_post() unslashes its input, so it must be slashed first. Without this, every
        // backslash in the block markup's JSON is stripped and the blocks come back invalid.
        $id = wp_insert_post(wp_slash([
            'post_type' => 'post',
            'post_status' => $status,
            'post_title' => sanitize_text_field((string) $input['title']),
            'post_content' => $this->markdown->convert((string) ($input['content'] ?? '')),
            'post_excerpt' => sanitize_textarea_field((string) ($input['excerpt'] ?? '')),
            'post_author' => get_current_user_id(),
        ]), true);

        if ($id instanceof WP_Error) {
            return $id;
        }

        return [
            'id' => (int) $id,
            'status' => $status,
            'url' => (string) get_permalink((int) $id),
            'edit_url' => admin_url('post.php?post='.(int) $id.'&action=edit'),
        ];
    }
}

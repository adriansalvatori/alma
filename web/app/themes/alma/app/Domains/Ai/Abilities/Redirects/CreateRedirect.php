<?php

namespace App\Domains\Ai\Abilities\Redirects;

use App\Domains\Seo\Redirects\RedirectManager;
use App\Domains\Seo\Redirects\RedirectRejected;
use WP_Error;

class CreateRedirect extends RedirectAbility
{
    public function __construct(private readonly RedirectManager $redirects) {}

    public function name(): string
    {
        return 'alma/create-redirect';
    }

    public function label(): string
    {
        return __('Create redirect', 'alma');
    }

    public function description(): string
    {
        return __('Redirect a path on this site to another path or an https URL. 301 for permanent moves, 302 for temporary ones. Refuses loops, system paths and the homepage. Live immediately.', 'alma');
    }

    protected function inputProperties(): array
    {
        return [
            'from' => ['type' => 'string', 'description' => 'A path on this site, e.g. /old-pricing/.'],
            'to' => ['type' => 'string', 'description' => 'A path on this site or an https:// URL.'],
            'status' => ['type' => 'integer', 'enum' => RedirectManager::STATUSES, 'default' => 301],
            'note' => ['type' => 'string', 'maxLength' => 255, 'description' => 'Why it exists, for whoever finds it later.'],
        ];
    }

    protected function requiredInput(): array
    {
        return ['from', 'to'];
    }

    public function execute(array $input): array|WP_Error
    {
        try {
            $redirect = $this->redirects->create(
                (string) $input['from'],
                (string) $input['to'],
                (int) ($input['status'] ?? 301),
                is_string($input['note'] ?? null) ? $input['note'] : 'Created by an AI agent',
                get_current_user_id(),
            );
        } catch (RedirectRejected $e) {
            return new WP_Error('alma_ai_redirect_rejected', $e->getMessage());
        }

        return [
            'id' => $redirect->id,
            'from' => $redirect->from_path,
            'to' => $redirect->to_url,
            'status' => $redirect->status_code,
        ];
    }
}

<?php

namespace App\Domains\Ai\Abilities\Redirects;

use App\Domains\Seo\Redirects\Redirect;
use App\Domains\Seo\Redirects\RedirectManager;
use WP_Error;

/**
 * Destructive: a deleted redirect sends its visitors (and search engines) to a 404. Confirmed in
 * two steps, the first showing what will be removed and how much traffic it still gets.
 */
class DeleteRedirect extends RedirectAbility
{
    public function __construct(private readonly RedirectManager $redirects) {}

    public function name(): string
    {
        return 'alma/delete-redirect';
    }

    public function label(): string
    {
        return __('Delete redirect', 'alma');
    }

    public function description(): string
    {
        return __('Delete an editor-managed redirect by id (from list-redirects). Two steps: the first call returns what would be removed and a confirmation token; repeat it with the token to delete. Code redirects cannot be deleted here.', 'alma');
    }

    public function destructive(): bool
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
            'id' => ['type' => 'integer', 'minimum' => 1],
        ];
    }

    protected function requiredInput(): array
    {
        return ['id'];
    }

    public function preview(array $input): array|WP_Error
    {
        $redirect = Redirect::query()->find((int) ($input['id'] ?? 0));

        if (! $redirect) {
            return new WP_Error('alma_ai_redirect_not_found', __('No editor-managed redirect with that id.', 'alma'));
        }

        return [
            'will_delete' => [
                'id' => $redirect->id,
                'from' => $redirect->from_path,
                'to' => $redirect->to_url,
                'status' => $redirect->status_code,
                'hits' => $redirect->hits,
                'last_hit_at' => $redirect->last_hit_at?->toIso8601String(),
            ],
            'warning' => __('Visitors to the source path will get a 404 once this is deleted.', 'alma'),
        ];
    }

    public function execute(array $input): array|WP_Error
    {
        $id = (int) ($input['id'] ?? 0);

        if (! $this->redirects->delete($id)) {
            return new WP_Error('alma_ai_redirect_not_found', __('No editor-managed redirect with that id.', 'alma'));
        }

        return ['deleted' => $id];
    }
}

<?php

namespace App\Domains\Ai\Abilities\Leads;

use App\Domains\Lead\Actions\ChangeLeadStatusAction;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use WP_Error;

/**
 * Move a lead through the pipeline. Goes through the Lead module's own action, so the change
 * lands in the lead's activity log as "ai:<user>", the same trail a person's change leaves.
 */
class UpdateLeadStatus extends LeadAbility
{
    public function __construct(private readonly ChangeLeadStatusAction $changeStatus) {}

    public function name(): string
    {
        return 'alma/update-lead-status';
    }

    public function label(): string
    {
        return __('Update lead status', 'alma');
    }

    public function description(): string
    {
        return __('Set a lead\'s status (new, contacted, qualified, won, lost, spam) by its id from query-leads. Recorded in the lead\'s activity log.', 'alma');
    }

    public function idempotent(): bool
    {
        return true;
    }

    protected function inputProperties(): array
    {
        return [
            'id' => ['type' => 'string', 'description' => 'The lead id (uuid) returned by query-leads.'],
            'status' => ['type' => 'string', 'enum' => array_map(fn (LeadStatus $status) => $status->value, LeadStatus::cases())],
        ];
    }

    protected function requiredInput(): array
    {
        return ['id', 'status'];
    }

    public function execute(array $input): array|WP_Error
    {
        $lead = Lead::query()->where('uuid', (string) ($input['id'] ?? ''))->first();
        $status = LeadStatus::tryFrom((string) ($input['status'] ?? ''));

        if (! $lead) {
            return new WP_Error('alma_ai_lead_not_found', __('No lead with that id.', 'alma'));
        }

        if (! $status) {
            return new WP_Error('alma_ai_lead_status', __('Unknown status.', 'alma'));
        }

        $previous = $lead->status->value;
        $user = wp_get_current_user();

        $this->changeStatus->execute($lead, $status, 'ai:'.($user->exists() ? $user->user_login : 'unknown'));

        return ['id' => $lead->uuid, 'from' => $previous, 'to' => $status->value];
    }
}

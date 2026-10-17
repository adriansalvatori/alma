<?php

namespace App\Domains\Lead\Actions;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadActivityLogger;

/**
 * Move a lead through the pipeline, leaving a line in its activity log saying who did it.
 */
class ChangeLeadStatusAction
{
    public function __construct(private LeadActivityLogger $activity) {}

    public function execute(Lead $lead, LeadStatus $status, string $actor): Lead
    {
        $previous = $lead->status;

        if ($previous === $status) {
            return $lead;
        }

        $lead->update(['status' => $status]);

        $this->activity->record($lead, 'StatusChanged', $actor, LeadActivity::SUCCEEDED, [
            'from' => $previous->value,
            'to' => $status->value,
        ]);

        return $lead;
    }
}

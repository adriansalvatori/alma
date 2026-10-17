<?php

namespace App\Domains\Lead\Listeners;

use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Services\LeadActivityLogger;

/**
 * Stage one of the audit log, written synchronously, before the response: the event fired. Each
 * after-response listener then writes its own outcome row. A dispatch with no outcome rows means
 * the after-response work never ran.
 */
class RecordLeadDispatch
{
    public function __construct(private LeadActivityLogger $activity) {}

    public function handle(LeadCaptured $event): void
    {
        $this->activity->record($event->lead, 'LeadCaptured', 'Lead', LeadActivity::DISPATCHED, [
            'form' => $event->lead->form_key,
        ]);
    }
}

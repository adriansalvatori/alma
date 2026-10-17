<?php

namespace App\Domains\Lead\Events;

use App\Domains\Lead\Models\Lead;
use Illuminate\Queue\SerializesModels;

/**
 * A real (non-spam) lead was stored. Notifications, webhooks and any CRM integration listen here.
 */
class LeadCaptured
{
    use SerializesModels;

    public function __construct(public Lead $lead) {}
}

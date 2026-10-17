<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writes the lead audit log. Never throws: losing an audit row is bad, failing the request or a
 * listener because of it is worse.
 */
class LeadActivityLogger
{
    /**
     * @param  array<string, mixed>  $detail
     */
    public function record(Lead|int $lead, string $event, string $actor, string $outcome, array $detail = []): ?LeadActivity
    {
        try {
            return LeadActivity::query()->create([
                'lead_id' => $lead instanceof Lead ? $lead->id : $lead,
                'event' => $event,
                'actor' => $actor,
                'outcome' => $outcome,
                'detail' => $detail === [] ? null : $detail,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Could not write lead activity', [
                'lead' => $lead instanceof Lead ? $lead->id : $lead,
                'event' => $event,
                'actor' => $actor,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

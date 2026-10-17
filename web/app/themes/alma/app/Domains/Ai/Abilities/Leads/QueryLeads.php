<?php

namespace App\Domains\Ai\Abilities\Leads;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\LeadQuery;
use Illuminate\Support\Carbon;

/**
 * Find leads. Uses the same filters as the Leads screen (LeadQuery), so an agent sees exactly
 * what a person filtering the list would: spam stays hidden unless asked for.
 */
class QueryLeads extends LeadAbility
{
    public function __construct(private readonly LeadQuery $leads) {}

    public function name(): string
    {
        return 'alma/query-leads';
    }

    public function label(): string
    {
        return __('Query leads', 'alma');
    }

    public function description(): string
    {
        return __('Find leads by text (name, email, company, phone), status, form or date, newest first. Returns contact details: personal data, handle accordingly.', 'alma');
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
            'status' => ['type' => 'string', 'enum' => array_map(fn (LeadStatus $status) => $status->value, LeadStatus::cases())],
            'form' => ['type' => 'string'],
            'since' => ['type' => 'string', 'description' => 'A date (YYYY-MM-DD); only leads captured on or after it.'],
            'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100, 'default' => 20],
        ];
    }

    public function execute(array $input): array
    {
        $query = $this->leads->build([
            'search' => $input['search'] ?? null,
            'status' => $input['status'] ?? null,
            'form' => $input['form'] ?? null,
        ]);

        if (is_string($input['since'] ?? null) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['since'])) {
            $query->where('created_at', '>=', Carbon::parse($input['since'])->startOfDay());
        }

        return $query
            ->limit(min(100, max(1, (int) ($input['limit'] ?? 20))))
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->uuid,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'message' => $lead->message !== null ? mb_strimwidth($lead->message, 0, 500, '…') : null,
                'form' => $lead->form_key,
                'status' => $lead->status->value,
                'source' => $this->source($lead),
                'created_at' => $lead->created_at?->toIso8601String(),
            ])
            ->all();
    }
}

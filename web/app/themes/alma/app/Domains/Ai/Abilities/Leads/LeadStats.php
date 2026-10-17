<?php

namespace App\Domains\Ai\Abilities\Leads;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;

/**
 * Counts, not people: how many leads a period brought in, by status, form and source.
 */
class LeadStats extends LeadAbility
{
    public function name(): string
    {
        return 'alma/lead-stats';
    }

    public function label(): string
    {
        return __('Lead stats', 'alma');
    }

    public function description(): string
    {
        return __('Count the leads captured in the last N days (default 30), broken down by status, form and traffic source. Spam is counted under status only and left out of the other totals.', 'alma');
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
            'days' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 365, 'default' => 30],
            'form' => ['type' => 'string', 'description' => 'Only this form key.'],
        ];
    }

    public function outputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'from' => ['type' => 'string'],
                'to' => ['type' => 'string'],
                'total' => ['type' => 'integer'],
                'by_status' => ['type' => 'object'],
                'by_form' => ['type' => 'object'],
                'by_source' => ['type' => 'object'],
            ],
            'required' => ['total', 'by_status', 'by_form', 'by_source'],
        ];
    }

    public function execute(array $input): array
    {
        $days = min(365, max(1, (int) ($input['days'] ?? 30)));
        $from = now()->subDays($days);
        $form = is_string($input['form'] ?? null) ? sanitize_key($input['form']) : '';

        $total = 0;
        $byStatus = [];
        $byForm = [];
        $bySource = [];

        Lead::query()
            ->select(['id', 'status', 'form_key', 'attribution'])
            ->where('created_at', '>=', $from)
            ->when($form !== '', fn ($query) => $query->where('form_key', $form))
            ->orderBy('id')
            ->chunk(500, function ($leads) use (&$total, &$byStatus, &$byForm, &$bySource) {
                foreach ($leads as $lead) {
                    $byStatus[$lead->status->value] = ($byStatus[$lead->status->value] ?? 0) + 1;

                    if ($lead->status === LeadStatus::Spam) {
                        continue;
                    }

                    $total++;
                    $byForm[$lead->form_key] = ($byForm[$lead->form_key] ?? 0) + 1;
                    $source = $this->source($lead);
                    $bySource[$source] = ($bySource[$source] ?? 0) + 1;
                }
            });

        arsort($byForm);
        arsort($bySource);

        return [
            'from' => $from->toIso8601String(),
            'to' => now()->toIso8601String(),
            'total' => $total,
            'by_status' => (object) $byStatus,
            'by_form' => (object) $byForm,
            'by_source' => (object) $bySource,
        ];
    }
}

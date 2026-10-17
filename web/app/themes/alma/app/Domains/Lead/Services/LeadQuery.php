<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * The filters shared by the admin list and the CSV export, so an export always contains exactly
 * what the list was showing.
 */
class LeadQuery
{
    /**
     * @param  array<string, mixed>  $filters  search, status, form
     * @return Builder<Lead>
     */
    public function build(array $filters): Builder
    {
        $query = Lead::query()->search(is_string($filters['search'] ?? null) ? $filters['search'] : null);

        $status = LeadStatus::tryFrom((string) ($filters['status'] ?? ''));

        if ($status !== null) {
            $query->where('status', $status->value);
        } else {
            // Spam stays out of the way unless asked for.
            $query->where('status', '!=', LeadStatus::Spam->value);
        }

        $form = sanitize_key((string) ($filters['form'] ?? ''));

        if ($form !== '') {
            $query->where('form_key', $form);
        }

        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /**
     * @return list<string>
     */
    public function forms(): array
    {
        return Lead::query()->distinct()->orderBy('form_key')->pluck('form_key')->all();
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        return Lead::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}

<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The filters shared by the admin list and the CSV export, so an export always contains exactly
 * what the list was showing.
 */
class LeadQuery
{
    /** The `source` filter value for leads whose first visit had no campaign or referrer. */
    public const DIRECT = 'direct';

    /**
     * The date windows, slug => days. Rolling windows counted back from now rather than calendar
     * days: a "today" resolved in UTC empties the list every evening in the Americas.
     */
    public const DATE_RANGES = ['24h' => 1, 'week' => 7, 'month' => 30];

    /**
     * @param  array<string, mixed>  $filters  search, status, form, source, date_range
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

        $source = trim((string) ($filters['source'] ?? ''));

        if ($source === self::DIRECT) {
            $query->whereNull('attribution->first->source');
        } elseif ($source !== '') {
            $query->where('attribution->first->source', $source);
        }

        $days = self::DATE_RANGES[(string) ($filters['date_range'] ?? '')] ?? 0;

        if ($days > 0) {
            $query->where('created_at', '>=', Carbon::now()->subDays($days));
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

    /**
     * Every first-touch channel and how many leads it brought, busiest first; leads with none are
     * counted under DIRECT. Spam left out. Counted in PHP because the JSON path reads differently
     * in MySQL and SQLite, and a marketing site's lead table is small enough to read through.
     *
     * @return array<string, int>
     */
    public function sources(): array
    {
        return Cache::remember('alma_lead_sources', 180, function (): array {
            $counts = [];

            $rows = Lead::query()
                ->where('status', '!=', LeadStatus::Spam->value)
                ->toBase()
                ->pluck('attribution');

            foreach ($rows as $json) {
                $attribution = is_string($json) ? json_decode($json, true) : null;
                $source = is_array($attribution) ? ($attribution['first']['source'] ?? null) : null;
                $key = is_string($source) && $source !== '' ? $source : self::DIRECT;

                $counts[$key] = ($counts[$key] ?? 0) + 1;
            }

            arsort($counts);

            return $counts;
        });
    }
}

<?php

namespace App\Domains\Lead\Hooks;

use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;

/**
 * Plugs leads into WordPress' own privacy tools (Tools → Export / Erase Personal Data), so a
 * GDPR request by email covers form submissions too.
 */
class LeadPrivacy
{
    private const PER_PAGE = 100;

    public function register(): void
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporter']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerEraser']);
    }

    /**
     * @param  array<string, mixed>  $exporters
     * @return array<string, mixed>
     */
    public function registerExporter(array $exporters): array
    {
        $exporters['alma-leads'] = [
            'exporter_friendly_name' => __('Form submissions (leads)', 'alma'),
            'callback' => [$this, 'export'],
        ];

        return $exporters;
    }

    /**
     * @param  array<string, mixed>  $erasers
     * @return array<string, mixed>
     */
    public function registerEraser(array $erasers): array
    {
        $erasers['alma-leads'] = [
            'eraser_friendly_name' => __('Form submissions (leads)', 'alma'),
            'callback' => [$this, 'erase'],
        ];

        return $erasers;
    }

    /**
     * @return array{data: list<array<string, mixed>>, done: bool}
     */
    public function export(string $email, int $page = 1): array
    {
        $leads = Lead::query()
            ->where('email', strtolower(trim($email)))
            ->orderBy('id')
            ->forPage($page, self::PER_PAGE)
            ->get();

        $data = $leads->map(fn (Lead $lead) => [
            'group_id' => 'alma-leads',
            'group_label' => __('Form submissions', 'alma'),
            'item_id' => 'alma-lead-'.$lead->uuid,
            'data' => array_values(array_filter([
                ['name' => __('Submitted', 'alma'), 'value' => (string) $lead->created_at?->toDateTimeString()],
                ['name' => __('Form', 'alma'), 'value' => $lead->form_key],
                ['name' => __('Name', 'alma'), 'value' => $lead->name],
                ['name' => __('Email', 'alma'), 'value' => $lead->email],
                ['name' => __('Phone', 'alma'), 'value' => (string) $lead->phone],
                ['name' => __('Company', 'alma'), 'value' => (string) $lead->company],
                ['name' => __('Message', 'alma'), 'value' => (string) $lead->message],
                ['name' => __('Other fields', 'alma'), 'value' => $lead->fields ? (string) wp_json_encode($lead->fields) : ''],
                ['name' => __('Consent given', 'alma'), 'value' => $lead->consent ? __('Yes', 'alma') : __('No', 'alma')],
            ], fn (array $item) => $item['value'] !== '')),
        ])->values()->all();

        return ['data' => $data, 'done' => $leads->count() < self::PER_PAGE];
    }

    /**
     * @return array{items_removed: bool, items_retained: bool, messages: list<string>, done: bool}
     */
    public function erase(string $email, int $page = 1): array
    {
        $ids = Lead::query()->where('email', strtolower(trim($email)))->pluck('id')->all();

        if ($ids !== []) {
            LeadActivity::query()->whereIn('lead_id', $ids)->delete();
            Lead::query()->whereIn('id', $ids)->delete();
        }

        return ['items_removed' => $ids !== [], 'items_retained' => false, 'messages' => [], 'done' => true];
    }
}

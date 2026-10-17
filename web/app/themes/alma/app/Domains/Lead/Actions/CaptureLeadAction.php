<?php

namespace App\Domains\Lead\Actions;

use App\Domains\Lead\Data\LeadData;
use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use App\Domains\Lead\Services\AttributionTracker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Store a lead and announce it.
 *
 * The form validates for the visitor's sake; this validates again because the form is not the
 * only possible caller, and nothing reaches the table unchecked.
 */
class CaptureLeadAction
{
    public function __construct(private AttributionTracker $attribution) {}

    /**
     * @throws ValidationException
     */
    public function execute(LeadData $data): Lead
    {
        $lead = $this->store($data, LeadStatus::New);

        Event::dispatch(new LeadCaptured($lead));

        return $lead;
    }

    /**
     * Keep a submission the spam guard caught, for review, without notifying anyone. Only called
     * when "store spam" is on.
     *
     * @throws ValidationException
     */
    public function recordSpam(LeadData $data, string $reason): Lead
    {
        $fields = $data->fields + ['_spam_reason' => $reason];

        return $this->store(LeadData::fromArray(['fields' => $fields] + $data->toArray()), LeadStatus::Spam);
    }

    private function store(LeadData $data, LeadStatus $status): Lead
    {
        $values = Validator::make($this->normalise($data), [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['max:255'],
            'phone' => ['nullable', 'max:64'],
            'company' => ['nullable', 'max:255'],
            'message' => ['nullable', 'max:10000'],
            'form_key' => ['required', 'max:100'],
        ])->validate();

        $attribution = $data->attribution ?? $this->attribution->current();

        return Lead::query()->create([
            ...$values,
            'fields' => $data->fields === [] ? null : $data->fields,
            'consent' => $data->consent,
            'consented_at' => $data->consent ? now() : null,
            'attribution' => $attribution->isEmpty() ? null : $attribution->toArray(),
            'ip_hash' => $data->ip !== null && $data->ip !== '' ? hash_hmac('sha256', $data->ip, (string) config('app.key')) : null,
            'user_agent' => $data->userAgent !== null ? mb_substr($data->userAgent, 0, 512) : null,
            'status' => $status,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function normalise(LeadData $data): array
    {
        $text = fn (?string $value) => $value === null || trim($value) === '' ? null : trim(strip_tags($value));

        return [
            'email' => strtolower(trim($data->email)),
            'name' => $text($data->name) ?? '',
            'phone' => $text($data->phone),
            'company' => $text($data->company),
            'message' => $data->message === null || trim($data->message) === '' ? null : trim(strip_tags($data->message)),
            'form_key' => sanitize_key($data->formKey) ?: 'default',
        ];
    }
}

<?php

namespace App\Domains\Lead\Services;

use App\Domains\Lead\Models\Lead;
use Illuminate\Database\Eloquent\Builder;

/**
 * CSV export of leads.
 *
 * Every value a visitor typed is escaped against formula injection: a name like "=HYPERLINK(...)"
 * would otherwise run as a formula when someone opens the export in Excel or Sheets.
 */
class LeadCsv
{
    public const COLUMNS = [
        'created_at' => 'Date',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'company' => 'Company',
        'message' => 'Message',
        'form_key' => 'Form',
        'status' => 'Status',
        'consent' => 'Consent',
        'first_source' => 'First source',
        'first_medium' => 'First medium',
        'first_campaign' => 'First campaign',
        'last_source' => 'Last source',
        'last_medium' => 'Last medium',
        'last_campaign' => 'Last campaign',
        'fields' => 'Other fields',
    ];

    /**
     * Neutralise a value a spreadsheet would treat as a formula.
     */
    public static function cell(mixed $value): string
    {
        $value = is_scalar($value) || $value === null ? (string) $value : (string) json_encode($value);

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
    }

    /**
     * @return list<string>
     */
    public function row(Lead $lead): array
    {
        $first = $lead->attribution['first'] ?? [];
        $last = $lead->attribution['last'] ?? [];

        $values = [
            'created_at' => $lead->created_at?->toDateTimeString(),
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'message' => $lead->message,
            'form_key' => $lead->form_key,
            'status' => $lead->status->value,
            'consent' => $lead->consent ? 'yes' : 'no',
            'first_source' => $first['source'] ?? null,
            'first_medium' => $first['medium'] ?? null,
            'first_campaign' => $first['campaign'] ?? null,
            'last_source' => $last['source'] ?? null,
            'last_medium' => $last['medium'] ?? null,
            'last_campaign' => $last['campaign'] ?? null,
            'fields' => $lead->fields === null || $lead->fields === [] ? null : $lead->fields,
        ];

        return array_map(self::cell(...), array_values($values));
    }

    /**
     * Write the export to $stream, a row at a time, so memory stays flat however many leads.
     *
     * @param  Builder<Lead>  $query
     * @param  resource  $stream
     */
    public function write(Builder $query, $stream): void
    {
        fputcsv($stream, array_values(self::COLUMNS), ',', '"', '');

        // lazyById pages on the id, so any other ordering has to go: oldest first is fine for a file.
        foreach ((clone $query)->reorder()->lazyById(500) as $lead) {
            fputcsv($stream, $this->row($lead), ',', '"', '');
        }
    }
}

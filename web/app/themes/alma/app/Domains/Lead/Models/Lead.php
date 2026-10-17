<?php

namespace App\Domains\Lead\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property string|null $message
 * @property array<string, mixed>|null $fields
 * @property string $form_key
 * @property bool $consent
 * @property Carbon|null $consented_at
 * @property array<string, mixed>|null $attribution
 * @property string|null $ip_hash
 * @property string|null $user_agent
 * @property LeadStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Lead extends Model
{
    protected $table = 'alma_leads';

    protected $guarded = ['id'];

    protected $attributes = [
        'status' => 'new',
        'form_key' => 'default',
        'consent' => false,
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'attribution' => 'array',
            'consent' => 'boolean',
            'consented_at' => 'datetime',
            'status' => LeadStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lead $lead) {
            $lead->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * @return HasMany<LeadActivity, $this>
     */
    public function activity(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'lead_id')->orderBy('id');
    }

    /**
     * @param  Builder<Lead>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';

        $query->where(fn (Builder $q) => $q
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('company', 'like', $like)
            ->orWhere('phone', 'like', $like));
    }

    /**
     * The JSON a webhook receives. Stable: integrations depend on these keys.
     *
     * @return array<string, mixed>
     */
    public function toWebhookPayload(): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'message' => $this->message,
            'fields' => $this->fields ?? (object) [],
            'form' => $this->form_key,
            'consent' => $this->consent,
            'consented_at' => $this->consented_at?->toIso8601String(),
            'attribution' => $this->attribution ?? (object) [],
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A provider event that has been handled. Its (gateway, event_id) unique key is the
 * idempotency guarantee: the same event can arrive any number of times and act once.
 *
 * @property int $id
 * @property string $gateway
 * @property string $event_id
 * @property string $type
 * @property int|null $payment_id
 * @property array<string, mixed>|null $payload
 */
class ProcessedWebhook extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'alma_payment_events';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}

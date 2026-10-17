<?php

namespace App\Domains\Payment\Models;

use App\Domains\Payment\Data\Money;
use App\Domains\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $gateway
 * @property string|null $reference
 * @property PaymentStatus $status
 * @property int $amount_minor
 * @property string $currency
 * @property string|null $description
 * @property string|null $customer_email
 * @property string|null $customer_name
 * @property array<string, mixed>|null $metadata
 * @property string|null $lead_uuid
 * @property string|null $success_url
 * @property string|null $cancel_url
 * @property string|null $failure_reason
 * @property Carbon|null $paid_at
 * @property Carbon|null $refunded_at
 */
class Payment extends Model
{
    protected $table = 'alma_payments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount_minor' => 'integer',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->uuid ??= (string) Str::uuid();
            $payment->status ??= PaymentStatus::Pending;
        });
    }

    public function money(): Money
    {
        return Money::of($this->amount_minor, $this->currency);
    }

    /**
     * Move to $status if the transition is allowed. Returns false (and changes nothing) when it
     * is not, e.g. a late "failed" event for a payment that already succeeded.
     */
    public function transitionTo(PaymentStatus $status): bool
    {
        if (! $this->status->canTransitionTo($status)) {
            return false;
        }

        $this->status = $status;

        if ($status === PaymentStatus::Succeeded) {
            $this->paid_at = now();
            $this->failure_reason = null;
        }

        if ($status === PaymentStatus::Refunded) {
            $this->refunded_at = now();
        }

        $this->save();

        return true;
    }
}

<?php

namespace App\Domains\Payment\Actions;

use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Enums\WebhookOutcome;
use App\Domains\Payment\Events\PaymentFailed;
use App\Domains\Payment\Events\PaymentRefunded;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\ProcessedWebhook;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Apply a verified provider event to its payment, exactly once.
 *
 * Everything happens in one transaction: recording the event ID, changing the status and the
 * synchronous listeners. If a listener throws, the whole thing rolls back and the endpoint
 * answers 500, so the provider retries and the retry is not mistaken for a duplicate.
 */
class HandleWebhookAction
{
    public function execute(string $gateway, PaymentEvent $event): WebhookOutcome
    {
        try {
            return DB::transaction(fn () => $this->apply($gateway, $event));
        } catch (UniqueConstraintViolationException) {
            // Two deliveries of the same event raced; the other one won.
            return WebhookOutcome::Duplicate;
        }
    }

    private function apply(string $gateway, PaymentEvent $event): WebhookOutcome
    {
        if (ProcessedWebhook::query()->where('gateway', $gateway)->where('event_id', $event->eventId)->exists()) {
            return WebhookOutcome::Duplicate;
        }

        $payment = Payment::query()
            ->where('gateway', $gateway)
            ->where('reference', $event->reference)
            ->lockForUpdate()
            ->first();

        ProcessedWebhook::query()->create([
            'gateway' => $gateway,
            'event_id' => $event->eventId,
            'type' => $event->type->value,
            'payment_id' => $payment?->id,
            'payload' => $event->payload,
        ]);

        $status = $event->type->status();

        if ($payment === null || $status === null) {
            return WebhookOutcome::Ignored;
        }

        // A signed event can still disagree with what was charged (a partial capture, a
        // misconfigured price). Never mark that paid silently.
        if ($status === PaymentStatus::Succeeded && $event->amount !== null && ! $event->amount->equals($payment->money())) {
            Log::warning('Payment webhook amount does not match the payment; not marking it paid.', [
                'payment' => $payment->uuid,
                'expected' => $payment->money()->toArray(),
                'received' => $event->amount->toArray(),
            ]);

            return WebhookOutcome::Ignored;
        }

        if (! $payment->transitionTo($status)) {
            return WebhookOutcome::Ignored;
        }

        $domainEvent = match ($status) {
            PaymentStatus::Succeeded => new PaymentSucceeded($payment),
            PaymentStatus::Failed => new PaymentFailed($payment),
            PaymentStatus::Refunded => new PaymentRefunded($payment),
            PaymentStatus::Pending => null,
        };

        if ($domainEvent !== null) {
            event($domainEvent);
        }

        return WebhookOutcome::Processed;
    }
}

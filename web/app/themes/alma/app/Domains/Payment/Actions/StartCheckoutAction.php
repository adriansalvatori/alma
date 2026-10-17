<?php

namespace App\Domains\Payment\Actions;

use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\StartedCheckout;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Exceptions\GatewayNotConfigured;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Models\Payment;
use Throwable;

/**
 * Record a pending payment, then ask the gateway for a checkout session.
 *
 * The local row exists before the provider is called, so a webhook can never arrive for a
 * payment the site has no record of, and a provider outage leaves a failed row to inspect
 * rather than nothing.
 */
class StartCheckoutAction
{
    public function __construct(private readonly GatewayManager $gateways) {}

    public function execute(CheckoutRequest $request, ?string $gatewayKey = null): StartedCheckout
    {
        $gateway = $this->gateways->gateway($gatewayKey);

        if (! $gateway->isConfigured()) {
            throw GatewayNotConfigured::named($gateway->key());
        }

        $payment = Payment::query()->create([
            'gateway' => $gateway->key(),
            'status' => PaymentStatus::Pending,
            'amount_minor' => $request->amount->minor,
            'currency' => $request->amount->currency,
            'description' => $request->description,
            'customer_email' => $request->customerEmail,
            'customer_name' => $request->customerName,
            'metadata' => $request->metadata,
            'lead_uuid' => $request->leadUuid,
            'success_url' => $request->successUrl,
            'cancel_url' => $request->cancelUrl,
        ]);

        try {
            $session = $gateway->createCheckout($request->withMetadata(['payment_uuid' => $payment->uuid]));
        } catch (Throwable $e) {
            $payment->update(['status' => PaymentStatus::Failed, 'failure_reason' => mb_substr($e->getMessage(), 0, 250)]);

            throw $e;
        }

        $payment->update(['reference' => $session->reference]);

        return new StartedCheckout($payment, $session);
    }
}

<?php

namespace App\Domains\Payment\Contracts;

use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\CheckoutSession;
use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Exceptions\InvalidSignature;
use Illuminate\Http\Request;

/**
 * A payment provider (Stripe, Wompi, Mercado Pago, ...). Implement this, register the driver
 * with GatewayManager::extend(), list it in config/payments.php, and the checkout action and
 * webhook endpoint work with it unchanged. See docs/payments.md.
 */
interface PaymentGateway
{
    /**
     * The key it is configured under in config/payments.php, also used in its webhook URL.
     */
    public function key(): string;

    /**
     * Human-readable name, shown to shoppers and in wp-admin.
     */
    public function label(): string;

    /**
     * Whether every credential the gateway needs is present. The webhook endpoint answers 503
     * for an unconfigured gateway rather than processing unverifiable payloads.
     */
    public function isConfigured(): bool;

    /**
     * Ask the provider for a hosted checkout (or a client secret for an embedded one).
     * $request->metadata['payment_uuid'] identifies the local payment; send it to the provider
     * so the webhook can be matched back if the provider's reference is not returned up front.
     */
    public function createCheckout(CheckoutRequest $request): CheckoutSession;

    /**
     * Verify the request really came from the provider, then translate it into a PaymentEvent.
     *
     * @throws InvalidSignature when the signature is missing, wrong or too old.
     */
    public function parseWebhook(Request $request): PaymentEvent;
}

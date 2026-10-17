<?php

namespace App\Domains\Payment\Gateways;

use App\Domains\Payment\Contracts\PaymentGateway;
use App\Domains\Payment\Data\CheckoutRequest;
use App\Domains\Payment\Data\CheckoutSession;
use App\Domains\Payment\Data\Money;
use App\Domains\Payment\Data\PaymentEvent;
use App\Domains\Payment\Enums\PaymentEventType;
use App\Domains\Payment\Exceptions\InvalidSignature;
use App\Domains\Payment\Support\WebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use ValueError;

/**
 * A gateway with no provider behind it, for local development, staging demos and tests.
 *
 * Checkout redirects to a signed local page with "Pay" and "Decline" buttons. Pressing one sends
 * a signed webhook through the same endpoint logic a real provider uses, so the whole flow
 * (pending payment, signature check, idempotency, events) runs end to end without an account
 * anywhere. Never available in production: there it reports itself unconfigured, so its
 * webhook answers 503 and its pages do not exist.
 */
class FakeGateway implements PaymentGateway
{
    public const SIGNATURE_HEADER = 'Alma-Fake-Signature';

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config = []) {}

    public static function available(): bool
    {
        return wp_get_environment_type() !== 'production';
    }

    public function key(): string
    {
        return (string) ($this->config['key'] ?? 'fake');
    }

    public function label(): string
    {
        return (string) ($this->config['label'] ?? __('Test payment (no real charge)', 'alma'));
    }

    public function isConfigured(): bool
    {
        return self::available() && $this->secret() !== '';
    }

    public function createCheckout(CheckoutRequest $request): CheckoutSession
    {
        $reference = 'fake_'.Str::lower(Str::random(24));

        return new CheckoutSession(
            gateway: $this->key(),
            reference: $reference,
            redirectUrl: URL::temporarySignedRoute('payments.fake.show', now()->addHour(), ['reference' => $reference]),
        );
    }

    public function parseWebhook(Request $request): PaymentEvent
    {
        $body = $request->getContent();

        if (! WebhookSignature::verify($body, $request->header(self::SIGNATURE_HEADER), $this->secret())) {
            throw InvalidSignature::for($this->key());
        }

        $data = json_decode($body, true);

        if (! is_array($data) || ! isset($data['id'], $data['type'], $data['reference'])) {
            throw InvalidSignature::for($this->key());
        }

        try {
            $type = PaymentEventType::from((string) $data['type']);
        } catch (ValueError) {
            throw InvalidSignature::for($this->key());
        }

        return new PaymentEvent(
            type: $type,
            eventId: (string) $data['id'],
            reference: (string) $data['reference'],
            amount: isset($data['amount'], $data['currency']) ? Money::of((int) $data['amount'], (string) $data['currency']) : null,
            payload: $data,
        );
    }

    /**
     * Build the signed webhook the fake provider "sends". Used by the confirmation page and tests.
     *
     * @param  array<string, mixed>  $payload
     * @return array{body: string, signature: string}
     */
    public function signedWebhook(array $payload): array
    {
        $body = (string) json_encode($payload);

        return ['body' => $body, 'signature' => WebhookSignature::sign($body, $this->secret())];
    }

    /**
     * PAYMENTS_FAKE_SECRET, or a secret derived from APP_KEY so it works with zero setup.
     */
    private function secret(): string
    {
        $secret = (string) ($this->config['secret'] ?? '');

        if ($secret !== '') {
            return $secret;
        }

        $key = (string) config('app.key');

        return $key === '' ? '' : hash_hmac('sha256', 'alma-fake-gateway', $key);
    }
}

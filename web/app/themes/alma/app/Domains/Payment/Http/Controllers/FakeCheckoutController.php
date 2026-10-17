<?php

namespace App\Domains\Payment\Http\Controllers;

use App\Domains\Payment\Actions\HandleWebhookAction;
use App\Domains\Payment\GatewayManager;
use App\Domains\Payment\Gateways\FakeGateway;
use App\Domains\Payment\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The hosted "checkout page" of the fake gateway. Both routes are signed and only exist outside
 * production (see routes/web.php).
 */
class FakeCheckoutController
{
    public function show(string $reference): View
    {
        $payment = $this->payment($reference);

        return view('payment::fake-checkout', [
            'payment' => $payment,
            'confirmUrl' => URL::temporarySignedRoute('payments.fake.confirm', now()->addHour(), ['reference' => $reference]),
        ]);
    }

    /**
     * Simulate the provider: send a signed webhook through the real verification and handling,
     * then send the shopper back to the site like a provider redirect would.
     */
    public function confirm(Request $request, string $reference, GatewayManager $gateways, HandleWebhookAction $handle): RedirectResponse
    {
        $payment = $this->payment($reference);
        $outcome = $request->input('outcome') === 'succeeded' ? 'succeeded' : 'failed';

        /** @var FakeGateway $gateway */
        $gateway = $gateways->gateway($payment->gateway);
        $webhook = $gateway->signedWebhook([
            'id' => 'evt_'.Str::lower(Str::random(24)),
            'type' => $outcome,
            'reference' => $reference,
            'amount' => $payment->amount_minor,
            'currency' => $payment->currency,
        ]);

        $delivery = Request::create('/api/payments/webhook/'.$payment->gateway, 'POST', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_'.strtoupper(str_replace('-', '_', FakeGateway::SIGNATURE_HEADER)) => $webhook['signature'],
        ], content: $webhook['body']);

        $handle->execute($payment->gateway, $gateway->parseWebhook($delivery));

        $target = $outcome === 'succeeded' ? $payment->success_url : $payment->cancel_url;

        return redirect()->away($target ?: home_url('/'));
    }

    private function payment(string $reference): Payment
    {
        $payment = Payment::query()->where('reference', $reference)->first();

        abort_if($payment === null || ! $payment->gateway || ! app(GatewayManager::class)->gateway($payment->gateway) instanceof FakeGateway, 404);

        return $payment;
    }
}

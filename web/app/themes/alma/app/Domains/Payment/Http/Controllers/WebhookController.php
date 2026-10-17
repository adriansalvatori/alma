<?php

namespace App\Domains\Payment\Http\Controllers;

use App\Domains\Payment\Actions\HandleWebhookAction;
use App\Domains\Payment\Exceptions\InvalidSignature;
use App\Domains\Payment\GatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/payments/webhook/{gateway}
 *
 * Fails closed: an unconfigured gateway answers 503 instead of trusting a payload it cannot
 * verify, and a bad signature answers 403. Providers retry non-2xx answers, so a duplicate
 * delivery answers 200 to stop the retries.
 */
class WebhookController
{
    public function __invoke(Request $request, string $gateway, GatewayManager $gateways, HandleWebhookAction $handle): JsonResponse
    {
        if (! $gateways->knows($gateway)) {
            return response()->json(['error' => 'unknown_gateway'], 404);
        }

        $instance = $gateways->gateway($gateway);

        if (! $instance->isConfigured()) {
            return response()->json(['error' => 'gateway_not_configured'], 503);
        }

        try {
            $event = $instance->parseWebhook($request);
        } catch (InvalidSignature) {
            return response()->json(['error' => 'invalid_signature'], 403);
        }

        return response()->json(['status' => $handle->execute($gateway, $event)->value]);
    }
}

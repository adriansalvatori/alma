<?php

namespace App\Domains\Lead\Listeners;

use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Services\LeadActivityLogger;
use App\Domains\Lead\Services\LeadSettings;
use App\Domains\Lead\Services\WebhookSigner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * POST every lead as JSON to one URL: the generic bridge to Zapier, Make, n8n or a CRM endpoint.
 * Runs after the response. Records its outcome and never throws.
 */
class SendLeadWebhook
{
    public const ACTOR = 'Webhook';

    public function __construct(
        private LeadSettings $settings,
        private LeadActivityLogger $activity,
        private WebhookSigner $signer,
    ) {}

    public function handle(LeadCaptured $event): void
    {
        $url = $this->settings->webhookUrl();

        if ($url === '') {
            $this->activity->record($event->lead, 'LeadCaptured', self::ACTOR, LeadActivity::SKIPPED, ['reason' => 'no webhook URL']);

            return;
        }

        $result = $this->deliver($event->lead, $url);

        $this->activity->record(
            $event->lead,
            'LeadCaptured',
            self::ACTOR,
            $result['ok'] ? LeadActivity::SUCCEEDED : LeadActivity::FAILED,
            array_diff_key($result, ['ok' => true]),
        );
    }

    /**
     * Send one lead to $url. Also used by the "Send test webhook" button.
     *
     * @return array{ok: bool, status?: int, signed: bool, error?: string, host: string}
     */
    public function deliver(Lead $lead, string $url, string $event = 'lead.captured'): array
    {
        $secret = $this->settings->webhookSecret();
        $body = (string) json_encode([
            'event' => $event,
            'site' => home_url('/'),
            'lead' => $lead->toWebhookPayload(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $headers = ['User-Agent' => 'Alma/'.config('alma.version', 'dev')];

        if ($secret !== '') {
            $headers[WebhookSigner::HEADER] = $this->signer->sign($body, $secret);
        }

        $result = ['signed' => $secret !== '', 'host' => (string) parse_url($url, PHP_URL_HOST)];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->withBody($body, 'application/json')
                ->post($url);

            $result['status'] = $response->status();

            if (! $response->successful()) {
                $result['error'] = Str::limit(trim($response->body()), 300);
            }

            return ['ok' => $response->successful()] + $result;
        } catch (Throwable $e) {
            Log::warning('Lead webhook failed', ['lead' => $lead->id, 'host' => $result['host'], 'error' => $e->getMessage()]);

            return ['ok' => false, 'error' => Str::limit($e->getMessage(), 300)] + $result;
        }
    }
}

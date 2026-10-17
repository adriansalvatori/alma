<?php

use App\Domains\Lead\Actions\CaptureLeadAction;
use App\Domains\Lead\Data\LeadData;
use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Listeners\SendLeadNotification;
use App\Domains\Lead\Listeners\SendLeadWebhook;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadActivity;
use App\Domains\Lead\Services\LeadSettings;
use App\Domains\Lead\Services\WebhookSigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\WordPress\WordPress;

uses(RefreshDatabase::class);

beforeEach(function () {
    // These tests describe production delivery; the non-production guard has its own tests below.
    WordPress::$environment = 'production';

    $this->lead = Lead::query()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'message' => 'Hello',
        'form_key' => 'contact',
    ]);
    $this->event = new LeadCaptured($this->lead);
});

function outcome(Lead $lead, string $actor): ?LeadActivity
{
    return LeadActivity::query()->where('lead_id', $lead->id)->where('actor', $actor)->latest('id')->first();
}

it('emails the recipients with the lead as reply-to', function () {
    app(LeadSettings::class)->save(['recipients' => "sales@example.test\nowner@example.test"]);

    app(SendLeadNotification::class)->handle($this->event);

    expect(WordPress::$mails)->toHaveCount(1)
        ->and(WordPress::$mails[0]['to'])->toBe(['sales@example.test', 'owner@example.test'])
        ->and(WordPress::$mails[0]['subject'])->toContain('Ada Lovelace')
        ->and(WordPress::$mails[0]['message'])->toContain('ada@example.com')
        ->and(WordPress::$mails[0]['headers'])->toContain('Reply-To: "Ada Lovelace" <ada@example.com>')
        ->and(outcome($this->lead, SendLeadNotification::ACTOR)->outcome)->toBe(LeadActivity::SUCCEEDED);
});

it('records a failed send instead of throwing', function () {
    WordPress::$mailSucceeds = false;

    app(SendLeadNotification::class)->handle($this->event);

    expect(outcome($this->lead, SendLeadNotification::ACTOR)->outcome)->toBe(LeadActivity::FAILED);
});

it('skips the email when nobody is to be notified', function () {
    WordPress::$options['admin_email'] = '';

    app(SendLeadNotification::class)->handle($this->event);

    expect(WordPress::$mails)->toBe([])
        ->and(outcome($this->lead, SendLeadNotification::ACTOR)->outcome)->toBe(LeadActivity::SKIPPED);
});

it('posts a signed webhook the receiver can verify', function () {
    config(['leads.webhook_url' => 'https://hooks.example.test/lead', 'leads.webhook_secret' => 'shh']);
    Http::fake(['hooks.example.test/*' => Http::response(['ok' => true], 200)]);

    app(SendLeadWebhook::class)->handle($this->event);

    Http::assertSent(function (Request $request) {
        $payload = json_decode($request->body(), true);

        return $request->url() === 'https://hooks.example.test/lead'
            && $payload['event'] === 'lead.captured'
            && $payload['lead']['email'] === 'ada@example.com'
            && $payload['lead']['id'] === $this->lead->uuid
            && (new WebhookSigner)->verify($request->body(), $request->header(WebhookSigner::HEADER)[0], 'shh');
    });

    expect(outcome($this->lead, SendLeadWebhook::ACTOR)->outcome)->toBe(LeadActivity::SUCCEEDED)
        ->and(outcome($this->lead, SendLeadWebhook::ACTOR)->detail)->toMatchArray(['status' => 200, 'signed' => true]);
});

it('records a rejected webhook as failed', function () {
    config(['leads.webhook_url' => 'https://hooks.example.test/lead']);
    Http::fake(['hooks.example.test/*' => Http::response('nope', 500)]);

    app(SendLeadWebhook::class)->handle($this->event);

    expect(outcome($this->lead, SendLeadWebhook::ACTOR))
        ->outcome->toBe(LeadActivity::FAILED)
        ->detail->toMatchArray(['status' => 500, 'error' => 'nope', 'signed' => false]);
});

it('records an unreachable webhook as failed instead of throwing', function () {
    config(['leads.webhook_url' => 'https://hooks.example.test/lead']);
    Http::fake(fn () => throw new ConnectionException('timed out'));

    app(SendLeadWebhook::class)->handle($this->event);

    expect(outcome($this->lead, SendLeadWebhook::ACTOR)->outcome)->toBe(LeadActivity::FAILED);
});

it('skips the webhook when none is configured', function () {
    app(SendLeadWebhook::class)->handle($this->event);

    Http::assertNothingSent();
    expect(outcome($this->lead, SendLeadWebhook::ACTOR)->outcome)->toBe(LeadActivity::SKIPPED);
});

it('writes the dispatch row in the request and the outcomes after the response', function () {
    config(['leads.webhook_url' => 'https://hooks.example.test/lead']);
    Http::fake(['hooks.example.test/*' => Http::response('', 204)]);

    $lead = app(CaptureLeadAction::class)->execute(LeadData::fromArray(['email' => 'grace@example.com']));

    expect(LeadActivity::query()->where('lead_id', $lead->id)->pluck('outcome')->all())->toBe([LeadActivity::DISPATCHED]);

    app()->terminate();

    expect(LeadActivity::query()->where('lead_id', $lead->id)->pluck('outcome', 'actor')->all())->toBe([
        'Lead' => LeadActivity::DISPATCHED,
        SendLeadNotification::ACTOR => LeadActivity::SUCCEEDED,
        SendLeadWebhook::ACTOR => LeadActivity::SUCCEEDED,
    ]);
});

it('ignores database destinations outside production', function () {
    WordPress::$environment = 'local';
    app(LeadSettings::class)->save(['recipients' => 'sales@example.test', 'webhook_url' => 'https://crm.example.test/hook']);
    Http::fake();

    app(SendLeadNotification::class)->handle($this->event);
    app(SendLeadWebhook::class)->handle($this->event);

    Http::assertNothingSent();
    expect(WordPress::$mails)->toBe([])
        ->and(outcome($this->lead, SendLeadNotification::ACTOR)->outcome)->toBe(LeadActivity::SKIPPED)
        ->and(outcome($this->lead, SendLeadWebhook::ACTOR)->outcome)->toBe(LeadActivity::SKIPPED);
});

it('delivers outside production to destinations set in this environment\'s .env', function () {
    WordPress::$environment = 'staging';
    config(['leads.recipients' => ['qa@example.test'], 'leads.webhook_url' => 'https://staging-crm.example.test/hook']);
    Http::fake(['*' => Http::response('', 200)]);

    app(SendLeadNotification::class)->handle($this->event);
    app(SendLeadWebhook::class)->handle($this->event);

    Http::assertSentCount(1);
    expect(WordPress::$mails[0]['to'])->toBe(['qa@example.test']);
});

it('honours LEAD_DELIVERY=always and never', function (string $mode, int $mails) {
    WordPress::$environment = 'local';
    config(['leads.delivery' => $mode]);
    app(LeadSettings::class)->save(['recipients' => 'sales@example.test']);

    app(SendLeadNotification::class)->handle($this->event);

    expect(WordPress::$mails)->toHaveCount($mails);
})->with([['always', 1], ['never', 0]]);

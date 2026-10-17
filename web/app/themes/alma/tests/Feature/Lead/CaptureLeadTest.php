<?php

use App\Domains\Lead\Actions\CaptureLeadAction;
use App\Domains\Lead\Data\Attribution;
use App\Domains\Lead\Data\LeadData;
use App\Domains\Lead\Data\Touch;
use App\Domains\Lead\Events\LeadCaptured;
use App\Domains\Lead\Models\Lead;
use App\Domains\Lead\Models\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    unset($_COOKIE['alma_attribution']);
});

it('stores a normalised lead and announces it', function () {
    Event::fake([LeadCaptured::class]);

    $lead = app(CaptureLeadAction::class)->execute(LeadData::fromArray([
        'name' => '  Ada <b>Lovelace</b> ',
        'email' => '  Ada@Example.COM ',
        'phone' => ' +44 20 7946 0000 ',
        'message' => "Hello\nthere",
        'form_key' => 'Pricing Demo',
        'consent' => true,
        'fields' => ['budget' => '10k'],
        'ip' => '203.0.113.9',
        'user_agent' => 'Test Browser',
    ]));

    expect($lead->email)->toBe('ada@example.com')
        ->and($lead->name)->toBe('Ada Lovelace')
        ->and($lead->phone)->toBe('+44 20 7946 0000')
        ->and($lead->form_key)->toBe('pricingdemo')
        ->and($lead->consent)->toBeTrue()
        ->and($lead->consented_at)->not->toBeNull()
        ->and($lead->fields)->toBe(['budget' => '10k'])
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->uuid)->toMatch('/^[0-9a-f-]{36}$/')
        ->and($lead->ip_hash)->toHaveLength(64)
        ->and($lead->ip_hash)->not->toContain('203.0.113.9');

    Event::assertDispatched(LeadCaptured::class, fn (LeadCaptured $event) => $event->lead->is($lead));
});

it('stamps the attribution from the visitor cookie when none is passed', function () {
    $_COOKIE['alma_attribution'] = json_encode((new Attribution(
        first: Touch::fromArray(['source' => 'google', 'medium' => 'cpc', 'campaign' => 'spring']),
        last: Touch::fromArray(['source' => 'newsletter', 'medium' => 'email']),
    ))->toArray());

    $lead = app(CaptureLeadAction::class)->execute(LeadData::fromArray(['email' => 'a@example.com']));

    expect($lead->attribution['first']['source'])->toBe('google')
        ->and($lead->attribution['first']['campaign'])->toBe('spring')
        ->and($lead->attribution['last']['source'])->toBe('newsletter');
});

it('stores no attribution for a direct visit', function () {
    $lead = app(CaptureLeadAction::class)->execute(LeadData::fromArray(['email' => 'a@example.com']));

    expect($lead->attribution)->toBeNull();
});

it('refuses an invalid email even when the form is bypassed', function () {
    app(CaptureLeadAction::class)->execute(LeadData::fromArray(['email' => 'not-an-email']));
})->throws(ValidationException::class);

it('records spam for review without announcing it', function () {
    Event::fake([LeadCaptured::class]);

    $lead = app(CaptureLeadAction::class)->recordSpam(LeadData::fromArray(['email' => 'bot@example.com']), 'honeypot');

    expect($lead->status)->toBe(LeadStatus::Spam)
        ->and($lead->fields['_spam_reason'])->toBe('honeypot')
        ->and(Lead::query()->count())->toBe(1);

    Event::assertNotDispatched(LeadCaptured::class);
});

<?php

use App\Domains\Lead\Services\AttributionTracker;

beforeEach(function () {
    unset($_COOKIE['alma_attribution']);
    $this->tracker = app(AttributionTracker::class);
});

it('records a campaign visit as a touch', function () {
    $touch = $this->tracker->touchFrom(
        ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'spring', 'gclid' => 'abc', 'page' => '2'],
        'https://www.google.com/',
        'https://example.test/pricing/?utm_source=google&gclid=abc',
    );

    expect($touch->toArray())->toMatchArray([
        'source' => 'google',
        'medium' => 'cpc',
        'campaign' => 'spring',
        'gclid' => 'abc',
        'referrer' => 'https://www.google.com/',
        'landing_page' => 'https://example.test/pricing/',
    ])->and($touch->capturedAt)->not->toBeNull();
});

it('records a visit from another site as a referral', function () {
    $touch = $this->tracker->touchFrom([], 'https://news.example.org/story', 'https://example.test/');

    expect($touch->source)->toBe('news.example.org')
        ->and($touch->medium)->toBe('referral');
});

it('ignores direct and internal visits', function (?string $referrer) {
    expect($this->tracker->touchFrom([], $referrer, 'https://example.test/about/'))->toBeNull();
})->with([
    'direct' => [null],
    'internal' => ['https://example.test/pricing/'],
    'internal, www' => ['https://www.example.test/pricing/'],
]);

it('keeps the first touch and replaces the last', function () {
    $this->tracker->track(['utm_source' => 'google'], null, 'https://example.test/');
    $this->tracker->track([], 'https://example.test/pricing/', 'https://example.test/about/');
    $attribution = $this->tracker->track(['utm_source' => 'newsletter', 'utm_medium' => 'email'], null, 'https://example.test/');

    expect($attribution->first->source)->toBe('google')
        ->and($attribution->last->source)->toBe('newsletter')
        ->and($this->tracker->current()->first->source)->toBe('google');
});

it('reads a missing or tampered cookie as empty', function (string $value) {
    $_COOKIE['alma_attribution'] = $value;

    expect($this->tracker->current()->isEmpty())->toBeTrue();
})->with(['empty' => '', 'garbage' => '{not json', 'oversized' => str_repeat('a', 5000)]);

it('strips markup and caps untrusted values', function () {
    $touch = $this->tracker->touchFrom(['utm_source' => '<script>x</script>'.str_repeat('a', 400)], null, 'https://example.test/');

    expect($touch->source)->not->toContain('<')
        ->and(mb_strlen($touch->source))->toBe(255);
});

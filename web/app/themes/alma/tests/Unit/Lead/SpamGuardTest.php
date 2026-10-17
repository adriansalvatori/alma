<?php

use App\Domains\Lead\Services\LeadSettings;
use App\Domains\Lead\Services\SpamGuard;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    config(['leads.spam.min_seconds' => 3, 'leads.spam.max_attempts' => 5, 'leads.blocklist' => null]);
    $this->guard = app(SpamGuard::class);
    $this->oldToken = $this->guard->issueToken(time() - 60);
});

it('passes a human submission', function () {
    expect($this->guard->inspect('', $this->oldToken, 'ada@example.com', '203.0.113.1'))->toBeNull();
});

it('catches a filled honeypot', function () {
    expect($this->guard->inspect('http://spam.example', $this->oldToken, 'ada@example.com', '203.0.113.1'))
        ->toBe(SpamGuard::HONEYPOT);
});

it('catches a missing or tampered token', function (string $token) {
    expect($this->guard->inspect('', $token, 'ada@example.com', '203.0.113.1'))->toBe(SpamGuard::TOKEN);
})->with(['missing' => '', 'tampered' => 'eyJpdiI6Im5vcGUifQ==']);

it('catches a submission faster than a human can type', function () {
    expect($this->guard->inspect('', $this->guard->issueToken(), 'ada@example.com', '203.0.113.1'))
        ->toBe(SpamGuard::TOO_FAST);
});

it('accepts an old token, because a page cache serves old tokens', function () {
    expect($this->guard->inspect('', $this->guard->issueToken(time() - 30 * DAY_IN_SECONDS), 'ada@example.com', '203.0.113.1'))
        ->toBeNull();
});

it('rate limits one IP without affecting another', function () {
    foreach (range(1, 5) as $attempt) {
        expect($this->guard->inspect('', $this->oldToken, 'ada@example.com', '203.0.113.1'))->toBeNull();
    }

    expect($this->guard->inspect('', $this->oldToken, 'ada@example.com', '203.0.113.1'))->toBe(SpamGuard::RATE_LIMITED)
        ->and($this->guard->inspect('', $this->oldToken, 'ada@example.com', '203.0.113.2'))->toBeNull();
});

it('fails open when the rate limiter is unavailable', function () {
    RateLimiter::shouldReceive('tooManyAttempts')->andThrow(new RuntimeException('cache down'));

    expect($this->guard->inspect('', $this->oldToken, 'ada@example.com', '203.0.113.1'))->toBeNull();
});

it('blocks listed emails, domains and their subdomains', function (string $email, bool $blocked) {
    app(LeadSettings::class)->save(['blocklist' => "spammer@example.org\nbad.test"]);

    expect($this->guard->blocklisted($email))->toBe($blocked);
})->with([
    'listed email' => ['Spammer@Example.org', true],
    'listed domain' => ['anyone@bad.test', true],
    'subdomain of a listed domain' => ['anyone@mail.bad.test', true],
    'lookalike domain' => ['anyone@notbad.test', false],
    'clean email' => ['ada@example.org', false],
]);

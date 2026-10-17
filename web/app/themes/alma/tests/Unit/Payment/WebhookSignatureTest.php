<?php

use App\Domains\Payment\Support\WebhookSignature;

it('verifies a payload it signed', function () {
    $header = WebhookSignature::sign('{"a":1}', 'secret', 1_000);

    expect(WebhookSignature::verify('{"a":1}', $header, 'secret', now: 1_100))->toBeTrue();
});

it('refuses a tampered body, a wrong secret or a missing header', function () {
    $header = WebhookSignature::sign('{"a":1}', 'secret', 1_000);

    expect(WebhookSignature::verify('{"a":2}', $header, 'secret', now: 1_000))->toBeFalse()
        ->and(WebhookSignature::verify('{"a":1}', $header, 'other', now: 1_000))->toBeFalse()
        ->and(WebhookSignature::verify('{"a":1}', null, 'secret', now: 1_000))->toBeFalse()
        ->and(WebhookSignature::verify('{"a":1}', 'garbage', 'secret', now: 1_000))->toBeFalse();
});

it('refuses a signature outside the tolerance window, so captured requests cannot be replayed', function () {
    $header = WebhookSignature::sign('{}', 'secret', 1_000);

    expect(WebhookSignature::verify('{}', $header, 'secret', now: 1_000 + WebhookSignature::TOLERANCE + 1))->toBeFalse()
        ->and(WebhookSignature::verify('{}', $header, 'secret', now: 1_000 - WebhookSignature::TOLERANCE - 1))->toBeFalse();
});

it('never verifies with an empty secret', function () {
    $header = WebhookSignature::sign('{}', '', 1_000);

    expect(WebhookSignature::verify('{}', $header, '', now: 1_000))->toBeFalse();
});

it('accepts any matching v1 while a secret is being rotated', function () {
    $current = WebhookSignature::sign('{}', 'new', 1_000);
    $header = 't=1000,v1='.str_repeat('0', 64).','.explode(',', $current)[1];

    expect(WebhookSignature::verify('{}', $header, 'new', now: 1_000))->toBeTrue();
});

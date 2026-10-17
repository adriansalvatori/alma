<?php

use App\Domains\Lead\Services\LeadSettings;

beforeEach(function () {
    $this->settings = app(LeadSettings::class);
});

it('falls back to defaults', function () {
    expect($this->settings->recipients())->toBe(['admin@example.test'])
        ->and($this->settings->retentionDays())->toBe(365)
        ->and($this->settings->storeSpam())->toBeFalse()
        ->and($this->settings->webhookUrl())->toBe('');
});

it('prefers the saved admin value over the default', function () {
    $this->settings->save(['webhook_url' => 'https://hooks.example.test/a', 'recipients' => 'a@example.test, b@example.test']);

    expect($this->settings->webhookUrl())->toBe('https://hooks.example.test/a')
        ->and($this->settings->recipients())->toBe(['a@example.test', 'b@example.test']);
});

it('lets config (.env) win over the admin value and locks the field', function () {
    $this->settings->save(['webhook_url' => 'https://hooks.example.test/admin']);
    config(['leads.webhook_url' => 'https://hooks.example.test/env']);

    $this->settings->save(['webhook_url' => 'https://hooks.example.test/changed']);

    expect($this->settings->webhookUrl())->toBe('https://hooks.example.test/env')
        ->and($this->settings->lockedByConfig('webhook_url'))->toBeTrue()
        ->and(get_option(LeadSettings::OPTION)['webhook_url'])->toBe('https://hooks.example.test/admin');
});

it('treats an explicit false in config as a pinned value', function () {
    $this->settings->save(['store_spam' => '1']);
    config(['leads.store_spam' => false]);

    expect($this->settings->storeSpam())->toBeFalse();
});

it('keeps the saved secret when the field is submitted empty', function () {
    $this->settings->save(['webhook_secret' => 'first']);
    $this->settings->save(['webhook_secret' => '']);

    expect($this->settings->webhookSecret())->toBe('first');
});

it('rejects invalid emails and non-http webhook URLs', function () {
    $this->settings->save(['recipients' => "ok@example.test\nnot-an-email", 'webhook_url' => 'javascript:alert(1)']);

    expect($this->settings->recipients())->toBe(['ok@example.test'])
        ->and($this->settings->webhookUrl())->toBe('');
});

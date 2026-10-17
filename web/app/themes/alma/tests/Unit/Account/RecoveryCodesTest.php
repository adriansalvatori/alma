<?php

use App\Domains\Account\Services\RecoveryCodes;

beforeEach(function () {
    $this->codes = app(RecoveryCodes::class);
    $this->user = account_user();
});

it('generates eight distinct codes and stores only hashes', function () {
    $codes = $this->codes->generate($this->user->ID);
    $stored = get_user_meta($this->user->ID, RecoveryCodes::META, true);

    expect($codes)->toHaveCount(8)
        ->and(array_unique($codes))->toHaveCount(8)
        ->and($stored)->toHaveCount(8)
        ->and(array_intersect($codes, $stored))->toBeEmpty();
});

it('accepts each code once', function () {
    [$code] = $this->codes->generate($this->user->ID);

    expect($this->codes->consume($this->user->ID, $code))->toBeTrue()
        ->and($this->codes->consume($this->user->ID, $code))->toBeFalse()
        ->and($this->codes->remaining($this->user->ID))->toBe(7);
});

it('ignores case, spaces and the dash when matching', function () {
    [$code] = $this->codes->generate($this->user->ID);

    expect($this->codes->consume($this->user->ID, ' '.strtoupper(str_replace('-', '', $code)).' '))->toBeTrue();
});

it('invalidates the old set when regenerating', function () {
    [$old] = $this->codes->generate($this->user->ID);
    $this->codes->generate($this->user->ID);

    expect($this->codes->consume($this->user->ID, $old))->toBeFalse();
});

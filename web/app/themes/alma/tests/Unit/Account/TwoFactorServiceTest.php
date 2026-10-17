<?php

use App\Domains\Account\Services\TwoFactorService;

beforeEach(function () {
    $this->twoFactor = app(TwoFactorService::class);
    $this->user = account_user();
});

it('only turns on after a valid code from the new secret', function () {
    $secret = $this->twoFactor->beginSetup($this->user->ID);

    expect($this->twoFactor->isEnabled($this->user->ID))->toBeFalse()
        ->and($this->twoFactor->confirmSetup($this->user->ID, '000000'))->toBeFalse()
        ->and($this->twoFactor->confirmSetup($this->user->ID, account_totp($secret)))->toBeTrue()
        ->and($this->twoFactor->isEnabled($this->user->ID))->toBeTrue()
        ->and($this->twoFactor->pendingSecret($this->user->ID))->toBeNull();
});

it('stores the secret encrypted', function () {
    $secret = account_enable_two_factor($this->user);

    expect(get_user_meta($this->user->ID, TwoFactorService::META_SECRET, true))->not->toContain($secret);
});

it('refuses a code that was already used', function () {
    $secret = account_enable_two_factor($this->user);
    $code = account_totp($secret);

    expect($this->twoFactor->verify($this->user->ID, $code))->toBeTrue()
        ->and($this->twoFactor->verify($this->user->ID, $code))->toBeFalse();
});

it('refuses codes that are not six digits', function (string $code) {
    account_enable_two_factor($this->user);

    expect($this->twoFactor->verify($this->user->ID, $code))->toBeFalse();
})->with(['', '12345', '1234567', 'abcdef']);

it('accepts a code with spaces in it', function () {
    $secret = account_enable_two_factor($this->user);
    $code = account_totp($secret);

    expect($this->twoFactor->verify($this->user->ID, substr($code, 0, 3).' '.substr($code, 3)))->toBeTrue();
});

it('treats a secret it cannot decrypt as not enabled', function () {
    account_enable_two_factor($this->user);
    update_user_meta($this->user->ID, TwoFactorService::META_SECRET, 'not-encrypted');

    expect($this->twoFactor->isEnabled($this->user->ID))->toBeFalse();
});

it('clears everything when disabled', function () {
    account_enable_two_factor($this->user);
    $this->twoFactor->disable($this->user->ID);

    expect($this->twoFactor->isEnabled($this->user->ID))->toBeFalse()
        ->and(get_user_meta($this->user->ID, TwoFactorService::META_SECRET, true))->toBe('');
});

it('renders a QR code as SVG', function () {
    $secret = $this->twoFactor->beginSetup($this->user->ID);

    expect($this->twoFactor->qrCodeSvg($this->user, $secret))->toContain('<svg');
});

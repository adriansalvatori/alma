<?php

use App\Domains\Account\Services\TwoFactorService;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = account_user();
    wp_set_current_user($this->user->ID);
    $this->twoFactor = app(TwoFactorService::class);
});

it('refuses anyone who is not logged in', function () {
    wp_set_current_user(0);

    Livewire::test('account.security-panel')->assertStatus(403);
});

it('turns 2FA on after confirming a code and shows eight recovery codes once', function () {
    $component = Livewire::test('account.security-panel')->call('begin')->assertSet('settingUp', true)->assertSee('<svg', false);

    $secret = $this->twoFactor->pendingSecret($this->user->ID);

    $component->set('code', '123456')->call('confirm')->assertHasErrors(['code']);
    $component->set('code', account_totp($secret))->call('confirm')->assertHasNoErrors()->assertSet('enabled', true);

    expect($component->get('recoveryCodes'))->toHaveCount(8)
        ->and($this->twoFactor->isEnabled($this->user->ID))->toBeTrue();

    Livewire::test('account.security-panel')->assertSet('recoveryCodes', []);
});

it('cannot be switched off without the password', function () {
    account_enable_two_factor($this->user);

    Livewire::test('account.security-panel')
        ->set('password', 'wrong')
        ->call('disable')
        ->assertHasErrors(['password'])
        ->assertSet('enabled', true);

    expect($this->twoFactor->isEnabled($this->user->ID))->toBeTrue();
});

it('switches off with the password', function () {
    account_enable_two_factor($this->user);

    Livewire::test('account.security-panel')
        ->set('password', 'correct-horse-battery-1')
        ->call('disable')
        ->assertHasNoErrors()
        ->assertSet('enabled', false);

    expect($this->twoFactor->isEnabled($this->user->ID))->toBeFalse();
});

it('regenerates recovery codes only with the password', function () {
    account_enable_two_factor($this->user);

    Livewire::test('account.security-panel')
        ->call('regenerate')
        ->assertHasErrors(['password'])
        ->assertSet('recoveryCodes', [])
        ->set('password', 'correct-horse-battery-1')
        ->call('regenerate')
        ->assertCount('recoveryCodes', 8);
});

it('keeps server-set state out of the client\'s reach', function () {
    Livewire::test('account.security-panel')->set('enabled', true);
})->throws(CannotUpdateLockedPropertyException::class);

<?php

use App\Domains\Account\Services\PendingChallenge;
use App\Domains\Account\Services\RecoveryCodes;
use App\Domains\Account\Services\TwoFactorService;
use Livewire\Livewire;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $_COOKIE = [];
    $this->user = account_user();
    $this->secret = account_enable_two_factor($this->user);
});

it('sends a browser with no pending login back to the login page', function () {
    Livewire::test('account.two-factor-challenge')->assertRedirect(route('login'));
});

it('logs the user in with a valid code, honouring remember me', function () {
    account_start_challenge($this->user, remember: true);

    Livewire::test('account.two-factor-challenge')
        ->set('code', account_totp($this->secret))
        ->call('verify')
        ->assertRedirect(route('dashboard'));

    expect(WordPress::$authCookies)->toBe([['user_id' => $this->user->ID, 'remember' => true]])
        ->and(app(PendingChallenge::class)->current())->toBeNull();
});

it('goes where wp-login.php was going', function () {
    account_start_challenge($this->user, redirectTo: 'https://example.test/wp/wp-admin/');

    Livewire::test('account.two-factor-challenge')
        ->set('code', account_totp($this->secret))
        ->call('verify')
        ->assertRedirect('https://example.test/wp/wp-admin/');
});

it('refuses a code from a timestep that was already used', function () {
    update_user_meta($this->user->ID, TwoFactorService::META_LAST_TIMESTEP, PHP_INT_MAX);
    account_start_challenge($this->user);

    Livewire::test('account.two-factor-challenge')
        ->set('code', account_totp($this->secret))
        ->call('verify')
        ->assertHasErrors(['code']);

    expect(WordPress::$authCookies)->toBeEmpty();
});

it('throws the user back to the password after five wrong codes', function () {
    account_start_challenge($this->user);
    $component = Livewire::test('account.two-factor-challenge');

    foreach (range(1, 4) as $attempt) {
        $component->set('code', '000000')->call('verify')->assertHasErrors(['code']);
    }

    $component->set('code', '000000')->call('verify')->assertRedirect(route('login'));

    expect(app(PendingChallenge::class)->current())->toBeNull()
        ->and(WordPress::$authCookies)->toBeEmpty();
});

it('accepts a recovery code exactly once', function () {
    [$code] = app(RecoveryCodes::class)->generate($this->user->ID);

    account_start_challenge($this->user);
    Livewire::test('account.two-factor-challenge')
        ->call('toggleRecovery')
        ->set('code', $code)
        ->call('verify')
        ->assertRedirect(route('dashboard'));

    WordPress::$authCookies = [];
    account_start_challenge($this->user);
    Livewire::test('account.two-factor-challenge')
        ->call('toggleRecovery')
        ->set('code', $code)
        ->call('verify')
        ->assertHasErrors(['code']);

    expect(WordPress::$authCookies)->toBeEmpty();
});

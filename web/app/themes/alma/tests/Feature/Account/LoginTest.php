<?php

use App\Domains\Account\Services\PendingChallenge;
use Livewire\Livewire;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $_COOKIE = [];
    $this->user = account_user();
});

it('logs a user in and sends them to the dashboard', function () {
    Livewire::test('account.login-form')
        ->set('login', 'jane@example.test')
        ->set('password', 'correct-horse-battery-1')
        ->set('remember', true)
        ->call('authenticate')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(WordPress::$authCookies)->toBe([['user_id' => $this->user->ID, 'remember' => true]])
        ->and(get_current_user_id())->toBe($this->user->ID);
});

it('gives one generic error for a wrong password and an unknown user', function (string $login, string $password) {
    Livewire::test('account.login-form')
        ->set('login', $login)
        ->set('password', $password)
        ->call('authenticate')
        ->assertHasErrors(['login'])
        ->assertSee('These details do not match our records.')
        ->assertSet('password', '');

    expect(WordPress::$authCookies)->toBeEmpty();
})->with([
    'wrong password' => ['jane', 'nope'],
    'unknown user' => ['nobody@example.test', 'correct-horse-battery-1'],
]);

it('locks the form after five failures', function () {
    $component = Livewire::test('account.login-form')->set('login', 'jane');

    foreach (range(1, 5) as $attempt) {
        $component->set('password', 'wrong')->call('authenticate');
    }

    $component->set('password', 'correct-horse-battery-1')
        ->call('authenticate')
        ->assertSee('Too many sign-in attempts');

    expect(WordPress::$authCookies)->toBeEmpty();
});

it('sends a 2FA user to the challenge without logging them in', function () {
    account_enable_two_factor($this->user);

    Livewire::test('account.login-form')
        ->set('login', 'jane')
        ->set('password', 'correct-horse-battery-1')
        ->set('remember', true)
        ->call('authenticate')
        ->assertRedirect(route('two-factor.challenge'));

    expect(WordPress::$authCookies)->toBeEmpty()
        ->and(app(PendingChallenge::class)->current()->remember)->toBeTrue();
});

<?php

use Livewire\Livewire;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    update_option('users_can_register', '1');
    update_option('default_role', 'subscriber');
});

function account_registration()
{
    return Livewire::test('account.register-form')
        ->set('name', 'Ada Lovelace')
        ->set('email', 'Ada.Lovelace+news@example.test')
        ->set('password', 'analytical-engine-1')
        ->set('password_confirmation', 'analytical-engine-1');
}

it('creates a subscriber with a unique login and signs them in', function () {
    WordPress::user(['user_login' => 'ada.lovelace', 'user_email' => 'other@example.test']);
    test()->travel(5)->seconds();

    $component = account_registration();
    test()->travel(5)->seconds();
    $component->call('register')->assertHasNoErrors()->assertRedirect(route('dashboard'));

    $user = get_user_by('email', 'ada.lovelace+news@example.test');

    expect($user->user_login)->toBe('ada.lovelace-2')
        ->and($user->roles)->toBe(['subscriber'])
        ->and($user->display_name)->toBe('Ada Lovelace')
        ->and(WordPress::$authCookies[0]['user_id'])->toBe($user->ID)
        ->and(did_action('register_new_user'))->toBe(1);
});

it('is not available when registration is closed', function () {
    update_option('users_can_register', '0');

    Livewire::test('account.register-form')->assertStatus(404);
});

it('rejects a form submitted faster than a person could', function () {
    account_registration()->call('register')->assertHasErrors(['email']);

    expect(get_user_by('email', 'ada.lovelace+news@example.test'))->toBeFalse();
});

it('rejects a filled honeypot', function () {
    $component = account_registration()->set('website', 'https://spam.test');
    test()->travel(5)->seconds();
    $component->call('register')->assertHasErrors(['email']);

    expect(get_user_by('email', 'ada.lovelace+news@example.test'))->toBeFalse();
});

it('does not reveal that an email is taken', function () {
    WordPress::user(['user_email' => 'ada.lovelace+news@example.test']);

    $component = account_registration();
    test()->travel(5)->seconds();
    $component->call('register')
        ->assertHasErrors(['email'])
        ->assertSee('We could not create an account with these details.');
});

it('requires a reasonable password', function () {
    $component = account_registration()->set('password', 'short')->set('password_confirmation', 'short');
    test()->travel(5)->seconds();
    $component->call('register')->assertHasErrors(['password']);
});

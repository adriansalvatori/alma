<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $_COOKIE = [];
    $this->user = account_user();
});

it('shows the login page to guests, marked noindex', function () {
    $this->get('/login')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Sign in to your account');
});

it('sends a logged-in user from the login page to the dashboard', function () {
    wp_set_current_user($this->user->ID);

    $this->get('/login')->assertRedirect(route('dashboard'));
});

it('sends a guest from the account area to the login page', function (string $path) {
    $this->get($path)->assertRedirect(route('login'));
})->with(['/dashboard', '/settings']);

it('shows the account area to a logged-in user, marked noindex', function (string $path) {
    wp_set_current_user($this->user->ID);

    $this->get($path)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
})->with(['/dashboard', '/settings']);

it('has no registration page when registration is closed', function () {
    update_option('users_can_register', '0');

    $this->get('/register')->assertNotFound();
});

it('only shows the challenge page to a browser with a pending login', function () {
    $this->get('/two-factor-challenge')->assertRedirect(route('login'));

    account_enable_two_factor($this->user);
    account_start_challenge($this->user);

    $this->get('/two-factor-challenge')->assertOk();
});

it('has no GET logout route: logging out is WordPress\' nonce-protected flow', function () {
    expect(Route::has('logout'))->toBeFalse();
});

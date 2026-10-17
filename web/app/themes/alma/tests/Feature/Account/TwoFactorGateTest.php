<?php

use App\Domains\Account\Hooks\TwoFactorGate;
use App\Domains\Account\Services\PendingChallenge;
use Tests\Support\WordPress\WordPress;

beforeEach(function () {
    $_COOKIE = [];
    $_POST = [];
    $_REQUEST = [];
    unset($GLOBALS['pagenow']);
    $this->user = account_user();
});

afterEach(function () {
    unset($GLOBALS['pagenow']);
    $_POST = [];
    $_REQUEST = [];
});

it('lets a user without 2FA through', function () {
    expect(wp_authenticate('jane', 'correct-horse-battery-1'))->toBeInstanceOf(WP_User::class);
});

it('refuses a password-only login for a user with 2FA and starts a challenge', function () {
    account_enable_two_factor($this->user);

    $result = wp_authenticate('jane', 'correct-horse-battery-1');

    expect($result)->toBeInstanceOf(WP_Error::class)
        ->and($result->get_error_code())->toBe(TwoFactorGate::ERROR)
        ->and(app(PendingChallenge::class)->current()->userId)->toBe($this->user->ID)
        ->and(WordPress::$authCookies)->toBeEmpty();
});

it('does not start a challenge for a wrong password', function () {
    account_enable_two_factor($this->user);

    expect(wp_authenticate('jane', 'wrong'))->toBeInstanceOf(WP_Error::class)
        ->and(app(PendingChallenge::class)->current())->toBeNull();
});

it('sends wp-login.php to the challenge and keeps where it was going', function () {
    account_enable_two_factor($this->user);
    $GLOBALS['pagenow'] = 'wp-login.php';
    $_POST['rememberme'] = 'forever';
    $_REQUEST['redirect_to'] = 'https://example.test/wp/wp-admin/';
    app(TwoFactorGate::class)->exitUsing(fn () => throw new RuntimeException('exited'));

    expect(fn () => wp_authenticate('jane', 'correct-horse-battery-1'))->toThrow(RuntimeException::class, 'exited');

    $pending = app(PendingChallenge::class)->current();

    expect(WordPress::$redirects[0]['location'])->toBe(route('two-factor.challenge'))
        ->and($pending->remember)->toBeTrue()
        ->and($pending->redirectTo)->toBe('https://example.test/wp/wp-admin/')
        ->and(WordPress::$authCookies)->toBeEmpty();
});

it('drops an off-site redirect_to', function () {
    account_enable_two_factor($this->user);
    $GLOBALS['pagenow'] = 'wp-login.php';
    $_REQUEST['redirect_to'] = 'https://evil.test/';
    app(TwoFactorGate::class)->exitUsing(fn () => null);

    wp_authenticate('jane', 'correct-horse-battery-1');

    expect(app(PendingChallenge::class)->current()->redirectTo)->toBeNull();
});

it('lets application passwords through', function () {
    account_enable_two_factor($this->user);
    do_action('application_password_did_authenticate', $this->user, []);

    expect(wp_authenticate('jane', 'correct-horse-battery-1'))->toBeInstanceOf(WP_User::class);
});

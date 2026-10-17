<?php

/*
 * Helpers for the Account module's tests. Prefixed account_ so they cannot collide with other
 * modules' helpers.
 */

use App\Domains\Account\Services\PendingChallenge;
use App\Domains\Account\Services\TwoFactorService;
use PragmaRX\Google2FA\Google2FA;
use Tests\Support\WordPress\WordPress;

if (! function_exists('account_user')) {
    /**
     * A user whose password is "correct-horse-battery-1".
     */
    function account_user(array $attributes = []): WP_User
    {
        return WordPress::user($attributes + [
            'user_login' => 'jane',
            'user_email' => 'jane@example.test',
            'user_pass' => 'correct-horse-battery-1',
            'display_name' => 'Jane',
        ]);
    }
}

if (! function_exists('account_enable_two_factor')) {
    /**
     * Turn 2FA on for $user and return the plain secret.
     */
    function account_enable_two_factor(WP_User $user): string
    {
        $secret = app(Google2FA::class)->generateSecretKey(32);

        update_user_meta($user->ID, TwoFactorService::META_SECRET, encrypt($secret));
        update_user_meta($user->ID, TwoFactorService::META_ENABLED, true);

        return $secret;
    }
}

if (! function_exists('account_totp')) {
    /**
     * The TOTP code for $secret, $offset timesteps from now.
     */
    function account_totp(string $secret, int $offset = 0): string
    {
        $google2fa = app(Google2FA::class);

        return $google2fa->oathTotp($secret, $google2fa->getTimestamp() + $offset);
    }
}

if (! function_exists('account_start_challenge')) {
    function account_start_challenge(WP_User $user, bool $remember = false, ?string $redirectTo = null): void
    {
        app(PendingChallenge::class)->start($user, $remember, $redirectTo);
    }
}

<?php

namespace App\Domains\Account\Hooks;

use App\Domains\Account\Services\PendingChallenge;
use App\Domains\Account\Services\TwoFactorService;
use Closure;
use WP_Error;
use WP_User;

/**
 * Enforce two-factor authentication where WordPress decides who you are: the `authenticate` filter.
 *
 * Every password login runs through it: wp-login.php, the theme's Livewire login, XML-RPC. When
 * the user has 2FA on, a correct password does not log them in. It starts a pending challenge
 * and the login is refused, so no auth cookie is ever issued before the second factor. A
 * session flag checked by route middleware (the previous design) left wp-admin and every other
 * WordPress screen open to anyone with the password.
 *
 * Application passwords are a separate credential and pass through untouched: they are how
 * scripts and MCP clients authenticate, and they cannot answer a TOTP prompt.
 */
class TwoFactorGate
{
    public const ERROR = 'alma_two_factor_required';

    protected ?bool $remember = null;

    protected Closure $exit;

    public function __construct(
        protected TwoFactorService $twoFactor,
        protected PendingChallenge $challenges,
    ) {
        $this->exit = static fn () => exit;
    }

    public function register(): void
    {
        add_filter('authenticate', [$this, 'intercept'], 100, 3);
    }

    /**
     * @param  WP_User|WP_Error|null  $user
     */
    public function intercept(mixed $user, mixed $username = '', mixed $password = ''): mixed
    {
        if (! $user instanceof WP_User || ! $this->twoFactor->isEnabled($user->ID)) {
            return $user;
        }

        if (did_action('application_password_did_authenticate')) {
            return $user;
        }

        $this->challenges->start($user, $this->remember ?? ! empty($_POST['rememberme']), $this->requestedRedirect());

        if ($this->onWpLogin()) {
            wp_safe_redirect(route('two-factor.challenge'));
            ($this->exit)();
        }

        return new WP_Error(self::ERROR, __('Enter the code from your authenticator app to finish signing in.', 'alma'));
    }

    /**
     * Run $callback (a wp_authenticate() call) with an explicit "remember me" choice, for logins
     * that do not post wp-login.php's rememberme field.
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function withRemember(bool $remember, Closure $callback): mixed
    {
        $this->remember = $remember;

        try {
            return $callback();
        } finally {
            $this->remember = null;
        }
    }

    /**
     * Replace exit() after the wp-login.php redirect. Only the tests need this.
     */
    public function exitUsing(Closure $exit): void
    {
        $this->exit = $exit;
    }

    protected function onWpLogin(): bool
    {
        return ($GLOBALS['pagenow'] ?? null) === 'wp-login.php';
    }

    /**
     * Where wp-login.php was going to send the user (usually wp-admin), kept for after the challenge.
     */
    protected function requestedRedirect(): ?string
    {
        if (! $this->onWpLogin() || empty($_REQUEST['redirect_to']) || ! is_string($_REQUEST['redirect_to'])) {
            return null;
        }

        return wp_validate_redirect(wp_unslash($_REQUEST['redirect_to']), '') ?: null;
    }
}

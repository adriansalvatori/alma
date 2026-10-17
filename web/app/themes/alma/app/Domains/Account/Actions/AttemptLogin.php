<?php

namespace App\Domains\Account\Actions;

use App\Domains\Account\Data\LoginResult;
use App\Domains\Account\Hooks\TwoFactorGate;
use WP_User;

/**
 * Check a username (or email) and password through WordPress' own authentication, so every
 * `authenticate` filter applies: the two-factor gate, and any security plugin a project adds.
 */
class AttemptLogin
{
    public function __construct(
        protected TwoFactorGate $gate,
        protected CompleteLogin $completeLogin,
    ) {}

    public function execute(string $login, #[\SensitiveParameter] string $password, bool $remember = false): LoginResult
    {
        $user = $this->gate->withRemember($remember, fn () => wp_authenticate($login, $password));

        if (is_wp_error($user)) {
            return $user->get_error_code() === TwoFactorGate::ERROR
                ? LoginResult::TwoFactorRequired
                : LoginResult::Failed;
        }

        if (! $user instanceof WP_User) {
            return LoginResult::Failed;
        }

        $this->completeLogin->execute($user, $remember);

        return LoginResult::Authenticated;
    }
}

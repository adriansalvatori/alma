<?php

namespace App\Domains\Account\Actions;

use App\Domains\Account\Data\PendingLogin;
use App\Domains\Account\Services\PendingChallenge;
use App\Domains\Account\Services\RecoveryCodes;
use App\Domains\Account\Services\TwoFactorService;

/**
 * Finish a pending login with a TOTP code or a recovery code.
 */
class VerifyTwoFactorChallenge
{
    public function __construct(
        protected PendingChallenge $challenges,
        protected TwoFactorService $twoFactor,
        protected RecoveryCodes $recoveryCodes,
        protected CompleteLogin $completeLogin,
    ) {}

    /**
     * True when the code was accepted and the user is now logged in.
     */
    public function execute(PendingLogin $pending, string $code, bool $isRecoveryCode = false): bool
    {
        $user = get_userdata($pending->userId);

        $valid = $user && ($isRecoveryCode
            ? $this->recoveryCodes->consume($user->ID, $code)
            : $this->twoFactor->verify($user->ID, $code));

        if (! $valid) {
            $this->challenges->recordFailure($pending);

            return false;
        }

        $this->challenges->clear();
        $this->completeLogin->execute($user, $pending->remember);

        return true;
    }
}

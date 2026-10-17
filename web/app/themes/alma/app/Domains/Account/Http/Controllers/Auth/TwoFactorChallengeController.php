<?php

namespace App\Domains\Account\Http\Controllers\Auth;

use App\Domains\Account\Services\PendingChallenge;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TwoFactorChallengeController
{
    public function show(PendingChallenge $challenges): View|RedirectResponse
    {
        if ($challenges->current() === null) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }
}

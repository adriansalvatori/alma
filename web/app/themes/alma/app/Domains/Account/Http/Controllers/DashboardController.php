<?php

namespace App\Domains\Account\Http\Controllers;

use App\Domains\Account\Services\TwoFactorService;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function show(TwoFactorService $twoFactor): View
    {
        $user = wp_get_current_user();

        return view('dashboard', [
            'user' => $user,
            'twoFactorEnabled' => $twoFactor->isEnabled($user->ID),
        ]);
    }
}

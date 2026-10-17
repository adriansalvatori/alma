<?php

namespace App\Domains\Account\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;

class LoginController
{
    public function show(): View
    {
        return view('auth.login');
    }
}

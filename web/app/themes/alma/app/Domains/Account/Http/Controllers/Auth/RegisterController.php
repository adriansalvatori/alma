<?php

namespace App\Domains\Account\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;

class RegisterController
{
    public function show(): View
    {
        // Settings → General → Membership. A closed site has no registration page at all.
        abort_unless((bool) get_option('users_can_register'), 404);

        return view('auth.register');
    }
}

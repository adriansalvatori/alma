<?php

namespace App\Domains\Account\Http\Controllers\Auth;

use App\Domains\Account\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }
}

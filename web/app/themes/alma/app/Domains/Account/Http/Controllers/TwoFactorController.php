<?php

namespace App\Domains\Account\Http\Controllers;

class TwoFactorController extends Controller
{
    public function challenge()
    {
        return view('auth.two-factor-challenge');
    }
}

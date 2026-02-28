<?php

namespace App\Http\Controllers;

class TwoFactorController extends Controller
{
    public function challenge()
    {
        return view('auth.two-factor-challenge');
    }
}

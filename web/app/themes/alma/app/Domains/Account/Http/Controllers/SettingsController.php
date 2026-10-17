<?php

namespace App\Domains\Account\Http\Controllers;

use Illuminate\Contracts\View\View;

class SettingsController
{
    public function show(): View
    {
        return view('settings');
    }
}

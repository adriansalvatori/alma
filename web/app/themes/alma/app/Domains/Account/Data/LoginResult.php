<?php

namespace App\Domains\Account\Data;

enum LoginResult
{
    case Authenticated;
    case TwoFactorRequired;
    case Failed;
}

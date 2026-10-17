<?php

namespace App\Domains\Account\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Account pages need a logged-in WordPress user. WordPress is the identity: there is no separate
 * Laravel guard, so this checks the auth cookie WordPress already validated.
 */
class Authenticate
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! is_user_logged_in()) {
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}

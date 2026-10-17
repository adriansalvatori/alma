<?php

namespace App\Domains\Account\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (is_user_logged_in()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}

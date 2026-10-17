<?php

namespace App\Domains\Account\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! is_user_logged_in()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

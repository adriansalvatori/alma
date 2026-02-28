<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user has 2FA enabled but hasn't verified this session
        $user = wp_get_current_user();
        $is2faEnabled = get_user_meta($user->ID, 'two_factor_enabled', true);
        $isVerified = $request->session()->get('two_factor_verified', false);

        if ($is2faEnabled && !$isVerified) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}

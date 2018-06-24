<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class FirstLogin
{
    /**
     * Handle an incoming request.
     * Check member first login
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $member = Auth::user()->member;

        if (!is_null($member) && $member->first_login) {
            return redirect('changePassword');
        }
        return $next($request);
    }
}

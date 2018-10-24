<?php

namespace App\Http\Middleware;

use App\Models\User;
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
        /** @var User $user */
        $user = Auth::user();

        if ($user->first_login) {
            return redirect('changePassword');
        }
        return $next($request);
    }
}

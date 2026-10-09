<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {

            if (Auth::guard($guard)->check()) {

                // ✅ Student redirect
                if ($guard === 'student') {
                    return redirect()->route('student.dashboard');
                }

                // ✅ Admin redirect
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}

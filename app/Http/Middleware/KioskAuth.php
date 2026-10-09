<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KioskAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (session('kiosk_authenticated') && session('kiosk_user_id') && session('kiosk_guard')) {
            return $next($request);
        }

        return redirect()->route('kiosk.sign-in');
    }
}

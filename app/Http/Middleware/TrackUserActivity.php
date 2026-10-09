<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Update last_activity_at only if it's been more than 5 minutes since the last update
            // This prevents excessive database writes
            if (!$user->last_activity_at || 
                now()->diffInMinutes($user->last_activity_at) >= 5) {
                $user->update(['last_activity_at' => now()]);
            }
        }

        return $next($request);
    }
}

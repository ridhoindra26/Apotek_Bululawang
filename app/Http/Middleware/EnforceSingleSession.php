<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnforceSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sid  = $request->session()->getId();

            // Initialize if empty (first request after login)
            if (!$user->current_session_id) {
                $user->forceFill(['current_session_id' => $sid])->save();
            } elseif ($user->current_session_id !== $sid) {
                // Session mismatch => user logged in elsewhere
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('auth.index', [
                    'forced' => 1,
                    'msg' => 'You have been logged out because your account was used on another device.'
                ]);
            }
        }

        return $next($request);
    }
}

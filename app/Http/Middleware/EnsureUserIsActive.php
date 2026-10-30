<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Ensure the authenticated user account is currently active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user === null) {
            $this->invalidateSession($request);

            return redirect()->route('login')->with('status', __('Your session has expired. Please log in again.'));
        }

        if ($user->isPending()) {
            $this->invalidateSession($request);

            return redirect()->route('login')->with('status', __(
                'Your account is pending approval. Please wait for an administrator to approve your registration.'
            ));
        }

        if (! $user->isActive()) {
            $this->invalidateSession($request);

            return redirect()->route('login')->with('status', __(
                'Your account has been disabled. Please contact an administrator.'
            ));
        }

        return $next($request);
    }

    protected function invalidateSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}

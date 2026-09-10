<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInvitationOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $hasPendingInvitation = $user->teamInvitations()
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->exists();

        if ($hasPendingInvitation) {
            // Allow the page to load because the global
            // onboarding component will display the modal.
            return $next($request);
        }

        if ($user->must_change_password) {
            return $next($request);
        }

        return $next($request);
    }
}

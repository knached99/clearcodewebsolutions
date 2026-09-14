<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
         * If the URL already contains current_team, use it.
         *
         * Example:
         *
         * /khaled-nacheds-team/dashboard
         */
        if ($request->route('current_team')) {
            URL::defaults([
                'current_team' => $request->route('current_team'),
            ]);

            return $next($request);
        }

        /*
         * For routes such as /login and /onboarding there is no
         * current_team route parameter.
         *
         * If an authenticated user has a valid current team,
         * provide it as the URL-generation default.
         *
         * This makes:
         *
         * route('dashboard')
         *
         * work while rendering the authenticated application.
         */
        if (Auth::check()) {
            $user = Auth::user();
            $team = $user->currentTeam;

            if ($team) {
                URL::defaults([
                    'current_team' => $team->slug,
                ]);
            }
        }

        return $next($request);
    }
}

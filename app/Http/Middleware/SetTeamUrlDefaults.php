<?php

namespace App\Http\Middleware;

use App\Models\TeamInvitation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    /**
     * Set the default URL parameters for team-based routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $team = $user->currentTeam
                ?? $user->fallbackTeam();

            if (! $team) {
                $invitation = TeamInvitation::query()
                    ->with('team')
                    ->whereRaw('LOWER(email) = ?', [
                        strtolower((string) $user->email),
                    ])
                    ->whereNull('accepted_at')
                    ->where(function ($query) {
                        $query
                            ->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->latest('id')
                    ->first();

                $team = $invitation?->team;
            }

            if ($team) {
                URL::defaults([
                    'current_team' => $team->slug,
                    'team' => $team->slug,
                ]);
            }
        }

        return $next($request);
    }
}
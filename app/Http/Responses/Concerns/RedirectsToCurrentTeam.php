<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Http\Request;

trait RedirectsToCurrentTeam
{
    /**
     * Get the redirect path for the user's current team.
     */
    protected function redirectPathForCurrentTeam(
        Request $request,
        string $fallback
    ): string {
        $user = $request->user();

        if (! $user) {
            return $fallback;
        }

        $team = $user->currentTeam;

        /*
         * No team means there is nowhere to send the user
         * to a team-specific dashboard.
         */
        if (! $team) {
            return $fallback;
        }

        return route('dashboard', [
            'current_team' => $team->slug,
        ]);
    }
}

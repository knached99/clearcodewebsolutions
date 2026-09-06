<?php

namespace App\Http\Responses\Concerns;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

trait RedirectsToCurrentTeam
{
    protected function redirectPathForCurrentTeam(
        Request $request,
        string $redirect
    ): string {
        $team = $this->currentTeam($request);

        URL::defaults([
            'current_team' => $team->slug,
            'team' => $team->slug,
        ]);

        return "/{$team->slug}{$redirect}";
    }

    protected function currentTeam(Request $request): Team
    {
        $user = $request->user();

        abort_if(! $user, 403);

        /*
         * Normal authenticated user.
         */
        $team = $user->currentTeam;

        if ($team) {
            return $team;
        }

        /*
         * User may already belong to a team but not have
         * current_team_id populated.
         */
        $team = $user->fallbackTeam();

        if ($team) {
            return $team;
        }

        /*
         * Newly invited user who has not accepted yet.
         *
         * The invitation's team is used only for URL generation.
         * Actual team membership is still created by the Accept action.
         */
        $invitation = TeamInvitation::query()
            ->with('team')
            ->whereRaw('LOWER(email) = ?', [
                Str::lower((string) $user->email),
            ])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($invitation?->team) {
            return $invitation->team;
        }

        abort(403);
    }
}
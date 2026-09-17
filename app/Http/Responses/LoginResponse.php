<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use App\Models\TeamInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    use RedirectsToCurrentTeam;

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse([
                'two_factor' => false,
            ], 200);
        }

        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        /*
         * Pending invitations ALWAYS take priority.
         *
         * We do not accept anything here.
         *
         * The user must explicitly choose Accept or Decline from
         * RequiredOnboarding.
         */
        if ($this->hasPendingInvitation($user)) {
            return redirect()->route('onboarding');
        }

        /*
         * If the user has a team, make sure their current team is set.
         */
        if (! $user->currentTeam) {
            $team = $user->teams()->first();

            if ($team) {
                $user->forceFill([
                    'current_team_id' => $team->id,
                ])->save();

                $user->setRelation('currentTeam', $team);
            }
        }

        /*
         * A user without a team cannot be sent to the team dashboard.
         */
        if (! $user->currentTeam) {
            return redirect()->route('onboarding');
        }

        /*
         * Explicitly provide current_team.
         *
         * Do NOT rely on route defaults here.
         */
        return redirect()->intended(
            route('dashboard', [
                'current_team' => $user->currentTeam->slug,
            ])
        );
    }

    protected function hasPendingInvitation($user): bool
    {
        $email = Str::lower(trim((string) $user->email));

        return TeamInvitation::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->exists();
    }
}

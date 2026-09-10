<?php

namespace App\Http\Responses;

use App\Http\Responses\Concerns\RedirectsToCurrentTeam;
use App\Models\TeamInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;
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

        /*
         * An invited user may not have a current team yet because
         * membership is only created after the invitation is accepted.
         *
         * Find the pending invitation so we can temporarily use its
         * team for URL generation and redirect into the application.
         */
        if ($user) {
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
                URL::defaults([
                    'current_team' => $invitation->team->slug,
                    'team' => $invitation->team->slug,
                ]);

                return redirect()->to(
                    '/'.
                    $invitation->team->slug.
                    Fortify::redirects('login')
                );
            }
        }

        return redirect()->intended(
            $this->redirectPathForCurrentTeam(
                $request,
                Fortify::redirects('login')
            )
        );
    }
}

<?php

namespace App\Livewire;

use App\Models\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RequiredOnboarding extends Component
{
    public bool $showPendingInvitationsModal = false;

    public bool $showChangePasswordModal = false;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        $this->syncOnboardingState();
    }

    /**
     * Determine which onboarding step must currently be displayed.
     */
    public function syncOnboardingState(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = false;

            return;
        }

        // Invitation step always comes first.
        if ($this->pendingInvitations->isNotEmpty()) {
            $this->showPendingInvitationsModal = true;
            $this->showChangePasswordModal = false;

            return;
        }

        // Once invitations are resolved, require the password change.
        if ($user->must_change_password) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = true;

            return;
        }

        // Onboarding is complete.
        $this->showPendingInvitationsModal = false;
        $this->showChangePasswordModal = false;
    }

    #[Computed]
    public function pendingInvitations()
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        $email = strtolower(trim($user->email));

        return TeamInvitation::query()
            ->with([
                'inviter',
                'team',
            ])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->get()
            ->map(function (TeamInvitation $invitation) {
                return [
                    'id' => $invitation->id,
                    'code' => $invitation->code,
                    'inviter_name' => $invitation->inviter?->name ?? 'Someone',
                    'team_name' => $invitation->team?->name ?? 'Unknown team',
                    'expires_at' => $invitation->expires_at,
                ];
            });
    }

    /**
     * Accept an invitation.
     */
    public function acceptInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        if (! $user) {
            return;
        }

        DB::transaction(function () use ($invitation, $user) {
            /*
             * Adjust this relationship/table to match your application.
             *
             * This assumes your User model has a teams() relationship.
             */
            $user->teams()->syncWithoutDetaching([
                $invitation->team_id,
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            /*
             * If your application stores the active team on the user,
             * set it here.
             *
             * Remove this block if your app uses another mechanism.
             */
            if ($user->current_team_id === null) {
                $user->update([
                    'current_team_id' => $invitation->team_id,
                ]);
            }
        });

        unset($this->pendingInvitations);

        $this->resetPasswordFields();

        // If there are more invitations, show those first.
        $this->syncOnboardingState();

        Flux::toast(
            text: 'Invitation accepted.',
            variant: 'success'
        );
    }

    /**
     * Decline an invitation.
     *
     * Declining deletes the invited account and logs the user out.
     */
    public function declineInvitation(string $code)
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        if (! $user) {
            return;
        }

        DB::transaction(function () use ($invitation, $user) {
            /*
             * Remove the membership if one somehow exists.
             */
            $user->teams()->detach($invitation->team_id);

            /*
             * Remove the invitation.
             */
            $invitation->delete();

            /*
             * Delete the temporary invited account.
             */
            $user->delete();
        });

        /*
         * Completely terminate the authenticated session.
         */
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        $this->redirect(
            route('login'),
            navigate: false
        );
    }

    /**
     * Change the temporary password.
     */
    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        /*
         * This action cannot be used to bypass the invitation step.
         */
        if ($this->pendingInvitations->isNotEmpty()) {
            throw ValidationException::withMessages([
                'newPassword' => 'You must resolve your invitation before changing your password.',
            ]);
        }

        /*
         * Never allow this action unless the account actually requires
         * a password change.
         */
        if (! $user->must_change_password) {
            $this->showChangePasswordModal = false;

            return;
        }

        $validated = $this->validate([
            'newPassword' => [
                'required',
                'string',
                'confirmed',
                Password::default(),
            ],
        ]);

        $user->update([
            'password' => Hash::make($validated['newPassword']),
            'must_change_password' => false,
        ]);

        $this->resetPasswordFields();

        $this->showPendingInvitationsModal = false;
        $this->showChangePasswordModal = false;

        Flux::toast(
            text: 'Your password has been updated.',
            variant: 'success'
        );

        $this->redirect(
            route('dashboard'),
            navigate: true
        );
    }

    /**
     * Find and validate a pending invitation for the authenticated user.
     */
    protected function findPendingInvitation(string $code): TeamInvitation
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $invitation = TeamInvitation::query()
            ->where('code', $code)
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (! $invitation) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation is no longer available.',
            ]);
        }

        /*
         * Make sure this invitation actually belongs to the
         * authenticated user's email address.
         */
        if (
            strtolower(trim($invitation->email)) !==
            strtolower(trim($user->email))
        ) {
            abort(403);
        }

        return $invitation;
    }

    protected function resetPasswordFields(): void
    {
        $this->reset([
            'newPassword',
            'newPassword_confirmation',
        ]);
    }

    public function render()
    {
        return view('livewire.required-onboarding');
    }
}
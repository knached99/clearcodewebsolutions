<?php

namespace App\Livewire;

use App\Models\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
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

        $this->openCorrectModal();
    }

    #[On('refresh-onboarding')]
    public function refreshOnboarding(): void
    {
        $this->syncOnboardingState();

        $this->openCorrectModal();
    }

    protected function syncOnboardingState(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = false;

            return;
        }

        // Make sure we are using the current database state.
        $user->refresh();

        unset($this->pendingInvitations);

        /*
         * Step 1:
         *
         * Pending invitations must always be resolved first.
         */
        if ($this->pendingInvitations->isNotEmpty()) {
            $this->showPendingInvitationsModal = true;
            $this->showChangePasswordModal = false;

            return;
        }

        /*
         * Step 2:
         *
         * Only users who were created with a temporary password
         * should have must_change_password = true.
         *
         * Existing users keep their existing password and skip
         * this modal completely.
         */
        if ($user->must_change_password) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = true;

            return;
        }

        /*
         * Onboarding is complete.
         */
        $this->showPendingInvitationsModal = false;
        $this->showChangePasswordModal = false;
    }

    protected function openCorrectModal(): void
    {
        if ($this->showPendingInvitationsModal) {
            Flux::modal('pending-invitations')->show();

            return;
        }

        if ($this->showChangePasswordModal) {
            Flux::modal('change-invitation-password')->show();
        }
    }

    #[Computed]
    public function pendingInvitations()
{
    $user = Auth::user();

    if (! $user) {
        return collect();
    }

    $email = strtolower(trim((string) $user->email));

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
        ->get();
}

    public function acceptInvitation(string $code): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $invitation = $this->findPendingInvitation($code);

        $team = $invitation->team;

        if (! $team) {
            throw ValidationException::withMessages([
                'invitation' => 'The team associated with this invitation no longer exists.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user, $team): void {
            /*
             * Add the user to the team using the invitation's role.
             */
            $user->teams()->syncWithoutDetaching([
                $team->id => [
                    'role' => $invitation->role->value,
                ],
            ]);

            /*
             * Mark the invitation accepted.
             */
            $invitation->forceFill([
                'accepted_at' => now(),
            ])->save();

            /*
             * The accepted team becomes the user's current team.
             */
            $user->forceFill([
                'current_team_id' => $team->id,
            ])->save();

            /*
             * Keep the in-memory relationship synchronized.
             */
            $user->setRelation('currentTeam', $team);
        });

        /*
         * Refresh so must_change_password reflects the actual
         * database value.
         *
         * Existing users will have false here and will therefore
         * skip the password modal.
         *
         * Newly-created invited users will have true here and
         * will be shown the password modal.
         */
        $user->refresh();

        unset($this->pendingInvitations);

        $this->resetPasswordFields();

        $this->syncOnboardingState();

        Flux::modal('pending-invitations')->close();

        if ($this->showChangePasswordModal) {
            Flux::modal('change-invitation-password')->show();
        }

        Flux::toast(
            text: 'Invitation accepted.',
            variant: 'success',
        );

        // if no pwd change is required (only for existing users with a password)
        // then on accepted invitation, will redirect to curr team's dashboard 
        if (!$this->showChangePasswordModal) {
            $user->refresh();

            if ($user->currentTeam) {
                $this->redirect(route('dashboard', ['current_team' => $user->currentTeam->slug]), navigate: true);
            }
            return; 
        }

        $this->redirect(route('onboarding'), navigate:true);
    }

    public function declineInvitation(string $code): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $invitation = $this->findPendingInvitation($code);

        DB::transaction(function () use ($invitation, $user): void {
            /*
             * The account is being deleted, so there is no reason
             * to switch teams.
             */
            $user->teams()->detach($invitation->team_id);

            $invitation->delete();

            $user->delete();
        });

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirect(
            route('login'),
            navigate: false,
        );
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        /*
         * Never allow the password step before all invitations
         * have been resolved.
         */
        if ($this->pendingInvitations->isNotEmpty()) {
            throw ValidationException::withMessages([
                'newPassword' => 'You must resolve your invitation before changing your password.',
            ]);
        }

        /*
         * If this is an existing user, there is no password-change
         * requirement. Close the modal and stop.
         */
        if (! $user->must_change_password) {
            $this->showChangePasswordModal = false;

            Flux::modal('change-invitation-password')->close();

            return;
        }

        $validated = $this->validate([
            'newPassword' => [
                'required',
                'string',
                'confirmed',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user->forceFill([
            'password' => $validated['newPassword'],
            'must_change_password' => false,
        ])->save();

        /*
         * Reload the user after updating the password.
         */
        $user->refresh();

        $this->resetPasswordFields();

        $this->syncOnboardingState();

        Flux::modal('change-invitation-password')->close();

        Flux::toast(
            text: 'Your password has been updated.',
            variant: 'success',
        );

        /*
         * Send the user to their current team's dashboard.
         */
        if ($user->currentTeam) {
            $this->redirect(
                route('dashboard', [
                    'current_team' => $user->currentTeam->slug,
                ]),
                navigate: true,
            );

            return;
        }

        $this->redirect(
            route('onboarding'),
            navigate: true,
        );
    }

    protected function findPendingInvitation(string $code): TeamInvitation
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $invitation = TeamInvitation::query()
            ->with('team')
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

        if (
            strtolower(trim((string) $invitation->email))
            !== strtolower(trim((string) $user->email))
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

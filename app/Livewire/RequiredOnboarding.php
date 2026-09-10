<?php

namespace App\Livewire;

use App\Enums\TeamRole;
use App\Models\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        unset($this->pendingInvitations);

        // Step 1 always takes priority.
        if ($this->pendingInvitations->isNotEmpty()) {
            $this->showPendingInvitationsModal = true;
            $this->showChangePasswordModal = false;

            return;
        }

        // Step 2 comes after invitations are resolved.
        if ($user->must_change_password) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = true;

            return;
        }

        // Onboarding complete.
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

    public function acceptInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        if (! $user) {
            return;
        }

        $team = $invitation->team;

        if (! $team) {
            throw ValidationException::withMessages([
                'invitation' => 'The team associated with this invitation no longer exists.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user, $team) {
            $user->teams()->syncWithoutDetaching([
                $team->id => [
                    'role' => TeamRole::Member->value,
                ],
            ]);

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $user->switchTeam($team);
        });

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
    }

    public function declineInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        if (! $user) {
            return;
        }

        DB::transaction(function () use ($invitation, $user) {
            // Do NOT switch teams here because the account is being deleted.
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

        if ($this->pendingInvitations->isNotEmpty()) {
            throw ValidationException::withMessages([
                'newPassword' => 'You must resolve your invitation before changing your password.',
            ]);
        }

        if (! $user->must_change_password) {
            $this->showChangePasswordModal = false;

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

        $user->update([
            'password' => Hash::make($validated['newPassword']),
            'must_change_password' => false,
        ]);

        $user->refresh();

        $this->resetPasswordFields();

        $this->syncOnboardingState();

        Flux::modal('change-invitation-password')->close();

        Flux::toast(
            text: 'Your password has been updated.',
            variant: 'success',
        );

        $this->redirect(
            route('dashboard'),
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

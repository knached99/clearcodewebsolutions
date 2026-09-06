<?php

use App\Models\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public bool $showPendingInvitationsModal = true;

    public bool $showChangePasswordModal = false;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        /*
         * An invitation is always the first mandatory step.
         */
        if ($this->pendingInvitations->isNotEmpty()) {
            $this->showPendingInvitationsModal = true;
            $this->showChangePasswordModal = false;

            return;
        }

        /*
         * If there are no pending invitations but the user still
         * needs to change their temporary password, show that modal.
         */
        if ($user->must_change_password) {
            $this->showPendingInvitationsModal = false;
            $this->showChangePasswordModal = true;
        }
    }

    #[Computed]
    public function pendingInvitations(): \Illuminate\Support\Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        $email = strtolower((string) $user->email);

        return TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviter_name' => $invitation->inviter->name,
                'team_name' => $invitation->team->name,
            ]);
    }

    public function acceptInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        DB::transaction(function () use ($user, $invitation): void {
            $team = $invitation->team;

            $team->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $invitation->role]
            );

            $invitation->update([
                'accepted_at' => now(),
            ]);

            $user->switchTeam($team);
        });

        /*
         * Close invitation modal and immediately open
         * the mandatory password-change modal.
         */
        $this->showPendingInvitationsModal = false;

        $this->resetPasswordFields();

        $this->showChangePasswordModal = true;

        unset($this->pendingInvitations);

        Flux::toast(
            variant: 'success',
            text: __('Invitation accepted.')
        );
    }

    public function declineInvitation(string $code): void
    {
        $invitation = $this->findPendingInvitation($code);

        $user = Auth::user();

        DB::transaction(function () use ($user, $invitation): void {
            /*
             * Remove any membership that may have been created
             * by an older version of the invitation flow.
             */
            $invitation->team
                ->memberships()
                ->where('user_id', $user->id)
                ->delete();

            $invitation->delete();

            /*
             * This account was created specifically for the invitation.
             */
            $user->delete();
        });

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->redirectRoute('login', navigate: true);
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        /*
         * Do not allow the password modal to be bypassed.
         */
        if (! $user->must_change_password) {
            $this->showChangePasswordModal = false;

            return;
        }

        $this->validate(
            [
                'newPassword' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::default(),
                ],
            ],
            [],
            [
                'newPassword' => __('new password'),
            ]
        );

        $user->forceFill([
            'password' => Hash::make($this->newPassword),
            'must_change_password' => false,
        ])->save();

        $this->resetPasswordFields();

        $this->showChangePasswordModal = false;

        Flux::toast(
            variant: 'success',
            text: __('Your password has been changed successfully.')
        );

        $this->redirectRoute('dashboard', navigate: true);
    }

    private function resetPasswordFields(): void
    {
        $this->reset([
            'newPassword',
            'newPassword_confirmation',
        ]);

        $this->resetValidation();
    }

    private function findPendingInvitation(string $code): TeamInvitation
    {
        $invitation = TeamInvitation::query()
            ->with('team')
            ->where('code', $code)
            ->whereNull('accepted_at')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'invitation' => [
                    __('This invitation has expired.'),
                ],
            ]);
        }

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (
            strtolower((string) $invitation->email)
            !== strtolower((string) $user->email)
        ) {
            throw ValidationException::withMessages([
                'invitation' => [
                    __('This invitation was sent to a different email address.'),
                ],
            ]);
        }

        return $invitation;
    }
}; ?>

<div>
    @if ($this->pendingInvitations->isNotEmpty())
        <flux:modal
            name="pending-invitations"
            wire:model="showPendingInvitationsModal"
            focusable
            :dismissible="false"
            class="max-w-lg"
        >
            <div
                data-test="pending-invitations-modal"
                class="space-y-6"
            >
                <div>
                    <flux:heading size="lg">
                        {{ __('Pending team invitations') }}
                    </flux:heading>

                    <flux:subheading>
                        {{ __('You must accept or decline this invitation before you can continue.') }}
                    </flux:subheading>
                </div>

                @error('invitation')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                <div class="grid gap-4">
                    @foreach ($this->pendingInvitations as $invitation)
                        <div
                            data-test="pending-invitation-row"
                            class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"
                        >
                            <div class="space-y-1">
                                <p class="font-medium">
                                    {{ $invitation['team_name'] }}
                                </p>

                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __(':inviter invited you to join this team.', [
                                        'inviter' => $invitation['inviter_name'],
                                    ]) }}
                                </flux:text>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <flux:button
                                    variant="filled"
                                    wire:click="declineInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                    data-test="pending-invitation-decline"
                                >
                                    {{ __('Decline') }}
                                </flux:button>

                                <flux:button
                                    variant="primary"
                                    wire:click="acceptInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                    data-test="pending-invitation-accept"
                                >
                                    {{ __('Accept') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </flux:modal>
    @endif

    @if (Auth::check() && Auth::user()->must_change_password)
        <flux:modal
            name="change-invitation-password"
            wire:model="showChangePasswordModal"
            focusable
            :dismissible="false"
            class="max-w-lg"
        >
            <form
                wire:submit="updatePassword"
                class="space-y-6"
            >
                <div>
                    <flux:heading size="lg">
                        {{ __('Create your new password') }}
                    </flux:heading>

                    <flux:subheading>
                        {{ __('For security, you must create a new password before you can continue.') }}
                    </flux:subheading>
                </div>

                <flux:input
                    wire:model="newPassword"
                    type="password"
                    :label="__('New password')"
                    autocomplete="new-password"
                    viewable
                />

                <flux:input
                    wire:model="newPassword_confirmation"
                    type="password"
                    :label="__('Confirm new password')"
                    autocomplete="new-password"
                    viewable
                />

                <flux:error name="newPassword" />

                <div class="flex justify-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                    >
                        {{ __('Save new password') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</div>
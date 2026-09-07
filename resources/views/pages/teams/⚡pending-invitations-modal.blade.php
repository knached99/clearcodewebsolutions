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

        /**
         * An invitation is always the first mandatory step.
         */
        if ($this->pendingInvitations->isNotEmpty()) {
            $this->showPendingInvitationsModal = true;
            $this->showChangePasswordModal = false;

            return;
        }

        /**
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

        /**
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
            /**
             * Remove any membership that may have been created
             * by an older version of the invitation flow.
             */
            $invitation->team
                ->memberships()
                ->where('user_id', $user->id)
                ->delete();

            $invitation->delete();

            /**
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

        /**
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
            class="w-[calc(100vw-2rem)] max-w-lg"
        >
            <div
                data-test="pending-invitations-modal"
                class="overflow-hidden rounded-2xl border border-purple-500/15 bg-black shadow-2xl shadow-purple-950/30"
            >
                {{-- Header --}}
                <div class="border-b border-purple-500/10 px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-purple-600/15 ring-1 ring-purple-500/20">
                            <flux:icon.users class="size-5 text-purple-400" />
                        </div>

                        <div class="min-w-0">
                            <flux:heading
                                size="lg"
                                class="text-white"
                            >
                                {{ __('Pending team invitations') }}
                            </flux:heading>

                            <flux:text class="mt-1 text-sm text-zinc-500">
                                {{ __('You must accept or decline this invitation before you can continue.') }}
                            </flux:text>
                        </div>
                    </div>
                </div>

                {{-- Invitations --}}
                <div class="space-y-3 p-6">
                    @error('invitation')
                        <div class="rounded-xl border border-purple-500/15 bg-purple-950/20 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <flux:icon.x-circle class="mt-0.5 size-4 shrink-0 text-purple-400" />
                                <p class="text-sm text-purple-300">
                                    {{ $message }}
                                </p>
                            </div>
                        </div>
                    @enderror

                    <div class="space-y-2">
                        @foreach ($this->pendingInvitations as $invitation)
                            <div
                                data-test="pending-invitation-row"
                                class="rounded-xl border border-purple-500/10 bg-purple-950/10 p-4 transition hover:border-purple-500/20 hover:bg-purple-950/15"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-purple-600/15">
                                        <flux:icon.building-office-2 class="size-4 text-purple-400" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-white">
                                            {{ $invitation['team_name'] }}
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-zinc-500">
                                            {{ __(':inviter invited you to join this team.', [
                                                'inviter' => $invitation['inviter_name'],
                                            ]) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <flux:button
                                        variant="outline"
                                        wire:click="declineInvitation('{{ $invitation['code'] }}')"
                                        wire:loading.attr="disabled"
                                        data-test="pending-invitation-decline"
                                        class="w-full cursor-pointer border-purple-500/15 bg-transparent text-zinc-400 hover:border-purple-500/30 hover:bg-purple-950/30 hover:text-white"
                                    >
                                        {{ __('Decline') }}
                                    </flux:button>

                                    <flux:button
                                        variant="primary"
                                        wire:click="acceptInvitation('{{ $invitation['code'] }}')"
                                        wire:loading.attr="disabled"
                                        data-test="pending-invitation-accept"
                                        class="w-full cursor-pointer bg-purple-600 hover:bg-purple-500"
                                    >
                                        {{ __('Accept') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="h-px flex-1 bg-purple-500/10"></div>

                        <span class="shrink-0 text-[10px] font-medium uppercase tracking-[0.16em] text-zinc-700">
                            {{ __('Required') }}
                        </span>

                        <div class="h-px flex-1 bg-purple-500/10"></div>
                    </div>
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
            class="w-[calc(100vw-2rem)] max-w-lg"
        >
            <div class="overflow-hidden rounded-2xl border border-purple-500/15 bg-black shadow-2xl shadow-purple-950/30">
                <form
                    wire:submit="updatePassword"
                    novalidate
                >
                    {{-- Header --}}
                    <div class="border-b border-purple-500/10 px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-purple-600/15 ring-1 ring-purple-500/20">
                                <flux:icon.lock-closed class="size-5 text-purple-400" />
                            </div>

                            <div class="min-w-0">
                                <flux:heading
                                    size="lg"
                                    class="text-white"
                                >
                                    {{ __('Create your new password') }}
                                </flux:heading>

                                <flux:text class="mt-1 text-sm text-zinc-500">
                                    {{ __('For security, you must create a new password before you can continue.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <div class="space-y-5 p-6">
                        <div class="rounded-xl border border-purple-500/10 bg-purple-950/10 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:icon.shield-check class="size-4 shrink-0 text-purple-400" />

                                <p class="text-xs leading-5 text-zinc-500">
                                    {{ __('Choose a strong password that you do not use anywhere else.') }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <flux:input
                                wire:model="newPassword"
                                type="password"
                                :label="__('New password')"
                                autocomplete="new-password"
                                viewable
                                :error="$errors->first('newPassword')"
                                class="w-full [&_input]:min-h-12 [&_input]:rounded-xl [&_input]:px-4 [&_input]:text-sm"
                            />

                            <flux:input
                                wire:model="newPassword_confirmation"
                                type="password"
                                :label="__('Confirm new password')"
                                autocomplete="new-password"
                                viewable
                                :error="$errors->first('newPassword_confirmation')"
                                class="w-full [&_input]:min-h-12 [&_input]:rounded-xl [&_input]:px-4 [&_input]:text-sm"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-4 border-t border-purple-500/10 pt-5">
                            <div class="flex items-center gap-2 text-xs text-zinc-600">
                                <flux:icon.lock-closed class="size-3.5" />
                                <span>{{ __('Your password is securely encrypted.') }}</span>
                            </div>

                            <flux:button
                                type="submit"
                                variant="primary"
                                wire:loading.attr="disabled"
                                class="cursor-pointer bg-purple-600 px-5 hover:bg-purple-500"
                            >
                                <span wire:loading.remove wire:target="updatePassword">
                                    {{ __('Save new password') }}
                                </span>

                                <span wire:loading wire:target="updatePassword">
                                    {{ __('Saving...') }}
                                </span>
                            </flux:button>
                        </div>
                    </div>
                </form>
            </div>
        </flux:modal>
    @endif
</div>
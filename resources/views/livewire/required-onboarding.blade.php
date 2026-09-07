<div>
    {{-- ============================================================
         STEP 1: ACCEPT / DECLINE INVITATION
         ============================================================ --}}

    @if ($this->pendingInvitations->isNotEmpty())
        <flux:modal
            name="pending-invitations"
            wire:model="showPendingInvitationsModal"
            focusable
            :dismissible="false"
            class="cc-onboarding-modal"
        >
            <div class="space-y-6">

                <div>
                    <flux:heading size="xl">
                        Team invitation
                    </flux:heading>

                    <flux:text class="mt-2">
                        You've been invited to join the following team.
                        You must accept or decline the invitation before
                        continuing.
                    </flux:text>
                </div>

                <div class="space-y-3">
                    @foreach ($this->pendingInvitations as $invitation)
                        <div
                            wire:key="invitation-{{ $invitation['id'] }}"
                            class="rounded-xl border border-white/10 bg-white/[0.04] p-4"
                        >
                            <div class="space-y-1">
                                <flux:heading size="sm">
                                    {{ $invitation['team_name'] }}
                                </flux:heading>

                                <flux:text>
                                    Invited by {{ $invitation['inviter_name'] }}
                                </flux:text>

                                @if ($invitation['expires_at'])
                                    <flux:text size="sm" class="opacity-60">
                                        Expires
                                        {{ \Illuminate\Support\Carbon::parse($invitation['expires_at'])->format('M j, Y') }}
                                    </flux:text>
                                @endif
                            </div>

                            <div class="mt-4 flex gap-2">
                                <flux:button
                                    variant="ghost"
                                    wire:click="declineInvitation('{{ $invitation['code'] }}')"
                                    wire:confirm="Are you sure you want to decline this invitation? Your invited account will be deleted."
                                >
                                    Decline
                                </flux:button>

                                <flux:button
                                    variant="primary"
                                    wire:click="acceptInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove wire:target="acceptInvitation">
                                        Accept invitation
                                    </span>

                                    <span wire:loading wire:target="acceptInvitation">
                                        Accepting...
                                    </span>
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:text size="sm" class="opacity-60">
                    You cannot continue until you respond to the invitation.
                </flux:text>

            </div>
        </flux:modal>
    @elseif (Auth::check() && Auth::user()->must_change_password)

        {{-- ============================================================
             STEP 2: CHANGE TEMPORARY PASSWORD
             ============================================================ --}}

        <flux:modal
            name="change-invitation-password"
            wire:model="showChangePasswordModal"
            focusable
            :dismissible="false"
            class="cc-onboarding-modal"
        >
            <form wire:submit="updatePassword" class="space-y-6">

                <div>
                    <flux:heading size="xl">
                        Change your password
                    </flux:heading>

                    <flux:text class="mt-2">
                        You're using a temporary password. You must create
                        a new password before continuing.
                    </flux:text>
                </div>

                <div class="space-y-4">

                    <flux:input
                        wire:model="newPassword"
                        label="New password"
                        type="password"
                        autocomplete="new-password"
                        required
                    />

                    <flux:input
                        wire:model="newPassword_confirmation"
                        label="Confirm new password"
                        type="password"
                        autocomplete="new-password"
                        required
                    />

                </div>

                @error('newPassword')
                    <flux:text class="text-red-400">
                        {{ $message }}
                    </flux:text>
                @enderror

                <div class="flex justify-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="updatePassword">
                            Update password
                        </span>

                        <span wire:loading wire:target="updatePassword">
                            Updating...
                        </span>
                    </flux:button>
                </div>

                <flux:text size="sm" class="opacity-60">
                    You cannot continue until your password has been changed.
                </flux:text>

            </form>
        </flux:modal>
    @endif
</div>
<div
    x-data="{
        passwordVisible: false,
        confirmationVisible: false,

        get passwordStrength() {
            const password = this.$wire.newPassword ?? '';

            let score = 0;

            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            return score;
        },

        get passwordStrengthLabel() {
            switch (this.passwordStrength) {
                case 0:
                    return '';
                case 1:
                    return 'Very weak';
                case 2:
                    return 'Weak';
                case 3:
                    return 'Fair';
                case 4:
                    return 'Strong';
                case 5:
                    return 'Very strong';
                default:
                    return '';
            }
        },

        get passwordsMatch() {
            const password = this.$wire.newPassword ?? '';
            const confirmation = this.$wire.newPassword_confirmation ?? '';

            return confirmation.length > 0 && password === confirmation;
        }
    }"
>
    {{-- ============================================================= --}}
    {{-- PENDING TEAM INVITATIONS --}}
    {{-- ============================================================= --}}

    <flux:modal
        name="pending-invitations"
        wire:model="showPendingInvitationsModal"
        focusable
        :dismissible="false"
        class="cc-onboarding-modal"
    >
        <div class="relative overflow-hidden rounded-2xl">
            {{-- Background glow --}}
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-64 w-64 rounded-full bg-purple-600/20 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-32 -left-32 h-64 w-64 rounded-full bg-violet-700/10 blur-3xl"
            ></div>

            <div class="relative space-y-6">

                {{-- Header --}}
                <div class="space-y-3">
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-purple-500/10 ring-1 ring-purple-500/20"
                    >
                        <flux:icon
                            name="user-plus"
                            class="size-6 text-purple-400"
                        />
                    </div>

                    <div class="space-y-1">
                        <flux:heading size="xl">
                            Team invitation
                        </flux:heading>

                        <flux:text class="text-zinc-400">
                            You have been invited to join the following team.
                            Please accept or decline the invitation to continue.
                        </flux:text>
                    </div>
                </div>

                {{-- Invitations --}}
                <div class="space-y-3">
                    @forelse ($this->pendingInvitations as $invitation)
                        <div
                            wire:key="invitation-{{ $invitation['id'] }}"
                            class="rounded-xl border border-purple-500/20 bg-white/[0.03] p-4"
                        >
                            <div class="flex items-start gap-3">

                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-purple-500/10"
                                >
                                    <flux:icon
                                        name="building-office-2"
                                        class="size-5 text-purple-400"
                                    />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <flux:heading
                                        size="sm"
                                        class="text-white"
                                    >
                                        {{ $invitation['team_name'] }}
                                    </flux:heading>

                                    <flux:text
                                        size="sm"
                                        class="mt-1 text-zinc-400"
                                    >
                                        Invited by
                                        <span class="text-zinc-200">
                                            {{ $invitation['inviter_name'] }}
                                        </span>
                                    </flux:text>

                                    @if ($invitation['expires_at'])
                                        <flux:text
                                            size="xs"
                                            class="mt-2 text-zinc-600"
                                        >
                                            Expires
                                            {{ $invitation['expires_at']->format('M j, Y') }}
                                        </flux:text>
                                    @endif
                                </div>
                            </div>

                            {{-- Invitation errors --}}
                            @error('invitation')
                                <div class="mt-3 flex items-start gap-2">
                                    <flux:icon
                                        name="exclamation-circle"
                                        class="mt-0.5 size-4 shrink-0 text-red-400"
                                    />

                                    <flux:text
                                        size="sm"
                                        class="text-red-400"
                                    >
                                        {{ $message }}
                                    </flux:text>
                                </div>
                            @enderror

                            {{-- Actions --}}
                            <div
                                class="mt-4 flex items-center justify-end gap-2"
                            >
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    class="cursor-pointer text-zinc-400 hover:bg-white/5 hover:text-white"
                                    wire:click="declineInvitation('{{ $invitation['code'] }}')"
                                    wire:confirm="Are you sure you want to decline this invitation? Your invited account will be deleted."
                                >
                                    Decline
                                </flux:button>

                                <flux:button
                                    type="button"
                                    variant="primary"
                                    class="cursor-pointer bg-purple-600 hover:bg-purple-500"
                                    wire:click="acceptInvitation('{{ $invitation['code'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="acceptInvitation"
                                >
                                    <span
                                        wire:loading.remove
                                        wire:target="acceptInvitation"
                                    >
                                        Accept invitation
                                    </span>

                                    <span
                                        wire:loading
                                        wire:target="acceptInvitation"
                                    >
                                        Accepting...
                                    </span>
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="rounded-xl border border-zinc-800 bg-white/[0.02] p-5 text-center"
                        >
                            <flux:text class="text-zinc-500">
                                No pending invitations.
                            </flux:text>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </flux:modal>


    {{-- ============================================================= --}}
    {{-- REQUIRED PASSWORD CHANGE --}}
    {{-- ============================================================= --}}

    <flux:modal
        name="change-invitation-password"
        wire:model="showChangePasswordModal"
        focusable
        :dismissible="false"
        class="cc-onboarding-modal"
    >
        <div class="relative overflow-hidden rounded-2xl">
            {{-- Background glow --}}
            <div
                class="pointer-events-none absolute -right-32 -top-32 h-64 w-64 rounded-full bg-purple-600/20 blur-3xl"
            ></div>

            <div
                class="pointer-events-none absolute -bottom-32 -left-32 h-64 w-64 rounded-full bg-violet-700/10 blur-3xl"
            ></div>

            <div class="relative space-y-6">

                {{-- Header --}}
                <div class="space-y-3">
                    <div
                        class="flex size-12 items-center justify-center rounded-xl bg-purple-500/10 ring-1 ring-purple-500/20"
                    >
                        <flux:icon
                            name="lock-closed"
                            class="size-6 text-purple-400"
                        />
                    </div>

                    <div class="space-y-1">
                        <flux:heading size="xl">
                            Create your password
                        </flux:heading>

                        <flux:text class="text-zinc-400">
                            For security, you must replace your temporary
                            password before continuing.
                        </flux:text>
                    </div>
                </div>


                {{-- Password form --}}
                <form
                    wire:submit="updatePassword"
                    class="space-y-5"
                >

                    {{-- New password --}}
                    <div class="space-y-2">
                        <div class="relative">
                            <flux:input
                                wire:model.live="newPassword"
                                label="New password"
                                ::type="passwordVisible ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="pr-11"
                            />

                            {{-- Password visibility toggle --}}
                            <button
                                type="button"
                                x-on:click="passwordVisible = !passwordVisible"
                                class="absolute right-3 top-[2.15rem] z-10 cursor-pointer text-zinc-500 transition hover:text-purple-400"
                                tabindex="-1"
                                aria-label="Toggle password visibility"
                            >
                                <flux:icon
                                    x-show="!passwordVisible"
                                    name="eye"
                                    class="size-5"
                                />

                                <flux:icon
                                    x-show="passwordVisible"
                                    name="eye-slash"
                                    class="size-5"
                                />
                            </button>
                        </div>

                        {{-- New password error --}}
                        @error('newPassword')
                            <div class="flex items-start gap-2 px-1">
                                <flux:icon
                                    name="exclamation-circle"
                                    class="mt-0.5 size-4 shrink-0 text-red-400"
                                />

                                <flux:text
                                    size="sm"
                                    class="text-red-400"
                                >
                                    {{ $message }}
                                </flux:text>
                            </div>
                        @enderror
                    </div>


                    {{-- Password strength --}}
                    <div
                        x-show="$wire.newPassword.length > 0"
                        x-transition
                        class="space-y-3 pt-1"
                    >
                        <div class="flex items-center justify-between">
                            <flux:text
                                size="sm"
                                class="text-zinc-500"
                            >
                                Password strength
                            </flux:text>

                            <flux:text
                                size="sm"
                                x-text="passwordStrengthLabel"
                                class="text-purple-400"
                            ></flux:text>
                        </div>

                        {{-- Strength bars --}}
                        <div class="flex gap-1.5">
                            <template
                                x-for="i in 5"
                                :key="i"
                            >
                                <div
                                    class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                    :class="
                                        i <= passwordStrength
                                            ? 'bg-purple-500'
                                            : 'bg-zinc-800'
                                    "
                                ></div>
                            </template>
                        </div>

                        {{-- Requirements --}}
                        <div
                            class="grid grid-cols-1 gap-1.5 sm:grid-cols-2"
                        >
                            {{-- 8 characters --}}
                            <div
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    $wire.newPassword.length >= 8
                                        ? 'text-purple-400'
                                        : 'text-zinc-600'
                                "
                            >
                                <span
                                    x-text="$wire.newPassword.length >= 8 ? '✓' : '○'"
                                    class="w-3"
                                ></span>

                                8+ characters
                            </div>

                            {{-- Lowercase --}}
                            <div
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    /[a-z]/.test($wire.newPassword)
                                        ? 'text-purple-400'
                                        : 'text-zinc-600'
                                "
                            >
                                <span
                                    x-text="/[a-z]/.test($wire.newPassword) ? '✓' : '○'"
                                    class="w-3"
                                ></span>

                                Lowercase letter
                            </div>

                            {{-- Uppercase --}}
                            <div
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    /[A-Z]/.test($wire.newPassword)
                                        ? 'text-purple-400'
                                        : 'text-zinc-600'
                                "
                            >
                                <span
                                    x-text="/[A-Z]/.test($wire.newPassword) ? '✓' : '○'"
                                    class="w-3"
                                ></span>

                                Uppercase letter
                            </div>

                            {{-- Number --}}
                            <div
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    /[0-9]/.test($wire.newPassword)
                                        ? 'text-purple-400'
                                        : 'text-zinc-600'
                                "
                            >
                                <span
                                    x-text="/[0-9]/.test($wire.newPassword) ? '✓' : '○'"
                                    class="w-3"
                                ></span>

                                Number
                            </div>

                            {{-- Special character --}}
                            <div
                                class="flex items-center gap-2 text-xs"
                                :class="
                                    /[^A-Za-z0-9]/.test($wire.newPassword)
                                        ? 'text-purple-400'
                                        : 'text-zinc-600'
                                "
                            >
                                <span
                                    x-text="/[^A-Za-z0-9]/.test($wire.newPassword) ? '✓' : '○'"
                                    class="w-3"
                                ></span>

                                Special character
                            </div>
                        </div>
                    </div>


                    {{-- Confirm password --}}
                    <div class="space-y-2">
                        <div class="relative">
                            <flux:input
                                wire:model.live="newPassword_confirmation"
                                label="Confirm new password"
                                ::type="confirmationVisible ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="pr-11"
                            />

                            {{-- Confirmation visibility toggle --}}
                            <button
                                type="button"
                                x-on:click="confirmationVisible = !confirmationVisible"
                                class="absolute right-3 top-[2.15rem] z-10 cursor-pointer text-zinc-500 transition hover:text-purple-400"
                                tabindex="-1"
                                aria-label="Toggle password confirmation visibility"
                            >
                                <flux:icon
                                    x-show="!confirmationVisible"
                                    name="eye"
                                    class="size-5"
                                />

                                <flux:icon
                                    x-show="confirmationVisible"
                                    name="eye-slash"
                                    class="size-5"
                                />
                            </button>
                        </div>

                        {{-- Confirmation error --}}
                        @error('newPassword_confirmation')
                            <div class="flex items-start gap-2 px-1">
                                <flux:icon
                                    name="exclamation-circle"
                                    class="mt-0.5 size-4 shrink-0 text-red-400"
                                />

                                <flux:text
                                    size="sm"
                                    class="text-red-400"
                                >
                                    {{ $message }}
                                </flux:text>
                            </div>
                        @enderror

                        {{-- Live match indicator --}}
                        <div
                            x-show="$wire.newPassword_confirmation.length > 0"
                            x-transition
                            class="pt-1"
                        >
                            <template x-if="passwordsMatch">
                                <div
                                    class="flex items-center gap-2 px-1 text-xs text-purple-400"
                                >
                                    <span>✓</span>
                                    <span>Passwords match</span>
                                </div>
                            </template>

                            <template x-if="!passwordsMatch">
                                <div
                                    class="flex items-center gap-2 px-1 text-xs text-zinc-600"
                                >
                                    <span>○</span>
                                    <span>Passwords do not match</span>
                                </div>
                            </template>
                        </div>
                    </div>


                    {{-- Submit --}}
                    <div class="pt-2">
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="w-full cursor-pointer bg-purple-600 hover:bg-purple-500"
                            wire:loading.attr="disabled"
                            wire:target="updatePassword"
                        >
                            <span
                                wire:loading.remove
                                wire:target="updatePassword"
                            >
                                Update password
                            </span>

                            <span
                                wire:loading
                                wire:target="updatePassword"
                            >
                                Updating password...
                            </span>
                        </flux:button>
                    </div>

                </form>
            </div>
        </div>
    </flux:modal>
</div>


{{-- ============================================================= --}}
{{-- LIVEWIRE NAVIGATION SUPPORT --}}
{{-- ============================================================= --}}

@script
<script>
    document.addEventListener('livewire:navigated', () => {
        $wire.refreshOnboarding();
    });
</script>
@endscript
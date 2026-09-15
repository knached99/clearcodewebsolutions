<div x-data="{
    passwordVisible: false,
    confirmationVisible: false,

    get passwordStrength() {
        const password = $wire.newPassword ?? '';

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
            case 1:
                return 'Very weak';
            case 2:
                return 'Weak';
            case 3:
                return 'Fair';
            case 4:
                return 'Good';
            case 5:
                return 'Strong';
            default:
                return '';
        }
    },

    get passwordsMatch() {
        const password = $wire.newPassword ?? '';
        const confirmation = $wire.newPassword_confirmation ?? '';

        if (!confirmation) {
            return null;
        }

        return password === confirmation;
    }
}">
    {{-- ================================================================
        PENDING INVITATIONS MODAL
    ================================================================= --}}
    <flux:modal name="pending-invitations" wire:model="showPendingInvitationsModal" focusable :dismissible="false"
        class="cc-onboarding-modal">
        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Header --}}
            <div class="border-b border-zinc-200 px-6 py-6 dark:border-zinc-800 sm:px-8">
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615A2.25 2.25 0 0 1 2.25 6.993V6.75" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                            Team invitation
                        </h2>

                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            You have a pending invitation to join a team.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="space-y-5 px-6 py-6 sm:px-8">
                @if ($this->pendingInvitations->isNotEmpty())
                    @foreach ($this->pendingInvitations as $invitation)
                        <div wire:key="pending-invitation-{{ $invitation->id }}"
                            class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-white/[0.03]">
                            {{-- Team --}}
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 21h16.5M4.5 18.75V8.25L12 3l7.5 5.25v10.5M8.25 21v-5.25h7.5V21M8.25 10.5h.008v.008H8.25V10.5Zm3.746 0h.008v.008h-.008V10.5Zm3.746 0h.008v.008h-.008V10.5Z" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                            {{ $invitation->team?->name ?? 'Unknown team' }}
                                        </p>

                                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">
                                            Team invitation
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="shrink-0 rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-500/10 dark:text-purple-300">
                                    Pending
                                </span>
                            </div>

                            {{-- Inviter --}}
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div
                                    class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-500">
                                        Invited by
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                                        {{ $invitation->inviter?->name ?? 'Team administrator' }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-lg border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                                    <p
                                        class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-500">
                                        Role
                                    </p>

                                    <p class="mt-1 text-sm font-medium capitalize text-zinc-900 dark:text-zinc-200">
                                        {{ $invitation->role?->value ?? ($invitation->role ?? 'Member') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Expiration --}}
                            @if ($invitation->expires_at)
                                <div class="mt-4 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>

                                    <span>
                                        Expires
                                        <span class="font-medium text-zinc-900 dark:text-zinc-200">
                                            {{ $invitation->expires_at->diffForHumans() }}
                                        </span>
                                    </span>
                                </div>
                            @endif

                            {{-- Error --}}
                            @if ($errors->has('invitation'))
                                <div
                                    class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/20 dark:bg-red-500/10">
                                    <p class="text-sm text-red-700 dark:text-red-400">
                                        {{ $errors->first('invitation') }}
                                    </p>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <flux:button type="button" variant="ghost"
                                    wire:click="declineInvitation('{{ $invitation->code }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="declineInvitation('{{ $invitation->code }}')"
                                    class="w-full text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-white sm:w-auto hover:cursor-pointer">
                                    Decline
                                </flux:button>

                                <flux:button type="button" variant="primary"
                                    wire:click="acceptInvitation('{{ $invitation->code }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="acceptInvitation('{{ $invitation->code }}')"
                                    class="w-full bg-purple-600 hover:bg-purple-500 sm:w-auto hover:cursor-pointer">
                                    <span wire:loading.remove
                                        wire:target="acceptInvitation('{{ $invitation->code }}')">
                                        Accept invitation
                                    </span>

                                    <span wire:loading wire:target="acceptInvitation('{{ $invitation->code }}')"
                                        class="flex items-center gap-2">
                                        <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>

                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4Z"></path>
                                        </svg>

                                        Accepting...
                                    </span>
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="py-8 text-center">
                        <div
                            class="mx-auto flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-white/5 dark:text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 8.511c.32.161.55.49.55.864v8.25a2.25 2.25 0 0 1-2.25 2.25H5.45a2.25 2.25 0 0 1-2.25-2.25v-8.25c0-.374.23-.703.55-.864l7.5-3.75a1.688 1.688 0 0 1 1.5 0l7.5 3.75Z" />
                            </svg>
                        </div>

                        <p class="mt-4 text-sm font-medium text-zinc-900 dark:text-white">
                            No pending invitations
                        </p>

                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            You're all caught up.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </flux:modal>


    {{-- ================================================================
        CHANGE PASSWORD MODAL
    ================================================================= --}}
    <flux:modal name="change-invitation-password" wire:model="showChangePasswordModal" focusable :dismissible="false"
        class="cc-onboarding-modal">
        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
            {{-- Header --}}
            <div class="border-b border-zinc-200 px-6 py-6 dark:border-zinc-800 sm:px-8">
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5a2.25 2.25 0 0 1 2.25 2.25v6a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18.75v-6a2.25 2.25 0 0 1 2.25-2.25Z" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                            Create your password
                        </h2>

                        <p class="mt-1.5 text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            Before continuing, create a secure password for your account.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <form wire:submit="updatePassword" class="space-y-6 px-6 py-6 sm:px-8">
                {{-- Password --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Password
                        </flux:label>

                        <div class="relative">
                            <flux:input x-bind:type="passwordVisible ? 'text' : 'password'"
                                wire:model.live="newPassword" autocomplete="new-password" class="pr-11" />

                            <button type="button" x-on:click="passwordVisible = !passwordVisible"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-zinc-500 transition hover:text-purple-600 dark:hover:text-purple-400"
                                :aria-label="passwordVisible ? 'Hide password' : 'Show password'">
                                {{-- Eye --}}
                                <svg x-show="!passwordVisible" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.8" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.364 4.5 12 4.5c4.636 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644C20.577 16.49 16.636 19.5 12 19.5c-4.636 0-8.577-3.01-9.964-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                {{-- Eye slash --}}
                                <svg x-show="passwordVisible" x-cloak xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 0 0 2.036 12.322a1.012 1.012 0 0 0 0 .644C3.423 17.49 7.364 20.5 12 20.5c1.737 0 3.374-.422 4.817-1.17M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.636 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644a10.47 10.47 0 0 1-1.784 3.032M6.228 6.228 3 3m3.228 3.228 3.19 3.19m5.355 5.355 3.249 3.249M14.535 14.535A3 3 0 0 1 9.465 9.465" />
                                </svg>
                            </button>
                        </div>

                        <flux:error name="newPassword" />
                    </flux:field>

                    {{-- Password strength --}}
                    <div x-show="$wire.newPassword" x-cloak class="mt-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                Password strength
                            </span>

                            <span class="text-xs font-medium text-purple-600 dark:text-purple-400"
                                x-text="passwordStrengthLabel"></span>
                        </div>

                        <div class="mt-2 flex gap-1.5">
                            <template x-for="segment in 5" :key="segment">
                                <div class="h-1.5 flex-1 rounded-full transition-colors"
                                    :class="segment <= passwordStrength ?
                                        'bg-purple-600 dark:bg-purple-500' :
                                        'bg-zinc-200 dark:bg-zinc-800'">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Requirements --}}
                    <div class="mt-4 space-y-2">
                        <p class="text-xs font-medium text-zinc-600 dark:text-zinc-400">
                            Password requirements
                        </p>

                        <div class="grid gap-2 sm:grid-cols-2">
                            {{-- 8 characters --}}
                            <div class="flex items-center gap-2 text-xs transition-colors"
                                :class="$wire.newPassword?.length >= 8 ?
                                    'text-purple-600 dark:text-purple-400' :
                                    'text-zinc-500 dark:text-zinc-600'">
                                <svg x-show="$wire.newPassword?.length >= 8" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg x-show="$wire.newPassword?.length < 8" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="size-3.5">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>

                                <span>At least 8 characters</span>
                            </div>

                            {{-- Uppercase --}}
                            <div class="flex items-center gap-2 text-xs transition-colors"
                                :class="/[A-Z]/.test($wire.newPassword ?? '') ?
                                    'text-purple-600 dark:text-purple-400' :
                                    'text-zinc-500 dark:text-zinc-600'">
                                <svg x-show="/[A-Z]/.test($wire.newPassword ?? '')" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg x-show="!/[A-Z]/.test($wire.newPassword ?? '')"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="size-3.5">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>

                                <span>One uppercase letter</span>
                            </div>

                            {{-- Lowercase --}}
                            <div class="flex items-center gap-2 text-xs transition-colors"
                                :class="/[a-z]/.test($wire.newPassword ?? '') ?
                                    'text-purple-600 dark:text-purple-400' :
                                    'text-zinc-500 dark:text-zinc-600'">
                                <svg x-show="/[a-z]/.test($wire.newPassword ?? '')" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg x-show="!/[a-z]/.test($wire.newPassword ?? '')"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="size-3.5">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>

                                <span>One lowercase letter</span>
                            </div>

                            {{-- Number --}}
                            <div class="flex items-center gap-2 text-xs transition-colors"
                                :class="/[0-9]/.test($wire.newPassword ?? '') ?
                                    'text-purple-600 dark:text-purple-400' :
                                    'text-zinc-500 dark:text-zinc-600'">
                                <svg x-show="/[0-9]/.test($wire.newPassword ?? '')" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg x-show="!/[0-9]/.test($wire.newPassword ?? '')"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="size-3.5">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>

                                <span>One number</span>
                            </div>

                            {{-- Symbol --}}
                            <div class="flex items-center gap-2 text-xs transition-colors sm:col-span-2"
                                :class="/[^A-Za-z0-9]/.test($wire.newPassword ?? '') ?
                                    'text-purple-600 dark:text-purple-400' :
                                    'text-zinc-500 dark:text-zinc-600'">
                                <svg x-show="/[^A-Za-z0-9]/.test($wire.newPassword ?? '')"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="size-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                </svg>

                                <svg x-show="!/[^A-Za-z0-9]/.test($wire.newPassword ?? '')"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" class="size-3.5">
                                    <circle cx="12" cy="12" r="9" />
                                </svg>

                                <span>One special character</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <flux:field>
                        <flux:label>
                            Confirm password
                        </flux:label>

                        <div class="relative">
                            <flux:input x-bind:type="confirmationVisible ? 'text' : 'password'"
                                wire:model.live="newPassword_confirmation" autocomplete="new-password"
                                class="pr-11" />

                            <button type="button" x-on:click="confirmationVisible = !confirmationVisible"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-zinc-500 transition hover:text-purple-600 dark:hover:text-purple-400"
                                :aria-label="confirmationVisible ? 'Hide password' : 'Show password'">
                                <svg x-show="!confirmationVisible" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.364 4.5 12 4.5c4.636 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644C20.577 16.49 16.636 19.5 12 19.5c-4.636 0-8.577-3.01-9.964-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                <svg x-show="confirmationVisible" x-cloak xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 0 0 2.036 12.322a1.012 1.012 0 0 0 0 .644C3.423 17.49 7.364 20.5 12 20.5c1.737 0 3.374-.422 4.817-1.17M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.636 0 8.577 3.01 9.964 7.178.07.21.07.434 0 .644a10.47 10.47 0 0 1-1.784 3.032M6.228 6.228 3 3m3.228 3.228 3.19 3.19m5.355 5.355 3.249 3.249M14.535 14.535A3 3 0 0 1 9.465 9.465" />
                                </svg>
                            </button>
                        </div>

                        <flux:error name="newPassword_confirmation" />
                    </flux:field>

                    {{-- Password match --}}
                    <div x-show="$wire.newPassword_confirmation" x-cloak class="mt-2 flex items-center gap-2 text-xs"
                        :class="passwordsMatch
                            ?
                            'text-purple-600 dark:text-purple-400' :
                            'text-red-600 dark:text-red-400'">
                        <template x-if="passwordsMatch">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="size-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                            </svg>
                        </template>

                        <template x-if="!passwordsMatch">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="size-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </template>

                        <span x-text="passwordsMatch ? 'Passwords match' : 'Passwords do not match'"></span>
                    </div>
                </div>

                {{-- Security note --}}
                <div
                    class="rounded-xl border border-purple-200 bg-purple-50 px-4 py-3.5 dark:border-purple-500/20 dark:bg-purple-500/5">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            class="mt-0.5 size-4 shrink-0 text-purple-600 dark:text-purple-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m0 3h.008v.008H12V15.75ZM10.5 3.75h3L21 12l-7.5 8.25h-3L3 12l7.5-8.25Z" />
                        </svg>

                        <p class="text-xs leading-5 text-purple-800 dark:text-purple-300">
                            Use a unique password that you don't use for other accounts.
                        </p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-1">
                    <flux:button type="submit" variant="primary" class="w-full bg-purple-600 hover:bg-purple-500"
                        wire:loading.attr="disabled" wire:target="updatePassword">
                        <span wire:loading.remove wire:target="updatePassword">
                            Continue
                        </span>

                        <span wire:loading wire:target="updatePassword"
                            class="flex items-center justify-center gap-2">
                            <svg class="size-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>

                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4Z"></path>
                            </svg>

                            Updating password...
                        </span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>


    {{-- ================================================================
        REFRESH ON NAVIGATION
    ================================================================= --}}
    @script
        <script>
            document.addEventListener('livewire:navigated', () => {
                $wire.refreshOnboarding();
            });
        </script>
    @endscript
</div>

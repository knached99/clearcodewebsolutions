<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Title;
use Livewire\Component;
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    #[Locked]
    public bool $canManagePasskeys;

    #[Locked]
    public array $passkeys = [];

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (
                Fortify::confirmsTwoFactorAuthentication()
                && is_null(auth()->user()->two_factor_confirmed_at)
            ) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();

            $this->requiresConfirmation = Features::optionEnabled(
                Features::twoFactorAuthentication(),
                'confirm'
            );
        }

        $this->canManagePasskeys = Features::canManagePasskeys();

        if ($this->canManagePasskeys) {
            $this->loadPasskeys();
        }
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset(
                'current_password',
                'password',
                'password_confirmation'
            );

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset(
            'current_password',
            'password',
            'password_confirmation'
        );

        Flux::toast(
            variant: 'success',
            text: __('Password updated.')
        );
    }

    /**
     * Load the user's passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = auth()->user()->passkeys()
            ->select([
                'id',
                'name',
                'credential',
                'created_at',
                'last_used_at',
            ])
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $passkeyId): void
    {
        $passkey = auth()->user()->passkeys()->findOrFail($passkeyId);

        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the passkey.
     */
    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $passkey = auth()->user()
            ->passkeys()
            ->findOrFail($this->deletingPasskeyId);

        $deletePasskey(auth()->user(), $passkey);

        $this->closeDeleteModal();

        $this->loadPasskeys();
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPasskeyId = null;
        $this->deletingPasskeyName = '';
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(
        DisableTwoFactorAuthentication $disableTwoFactorAuthentication
    ): void {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
}; ?>

<section
    x-data="{
        htmlIsDark: document.documentElement.classList.contains('dark'),
        observer: null,

        init() {
            this.observer = new MutationObserver(() => {
                this.htmlIsDark = document.documentElement.classList.contains('dark');
            });

            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });

            this.$nextTick(() => {
                this.htmlIsDark = document.documentElement.classList.contains('dark');
            });
        },

        get isLightMode() {
            return !this.htmlIsDark;
        }
    }"
    :class="{ 'light': isLightMode }"
    class="cc-security-settings w-full transition-colors duration-300"
>
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Security settings') }}
    </flux:heading>

    <x-pages::settings.layout
        :heading="__('Security')"
        :subheading="__('Manage your password, authentication, and passkeys.')"
    >
        <div class="overflow-hidden rounded-2xl border border-white/[0.08] bg-zinc-950/70 shadow-xl shadow-black/10">

            {{-- Password --}}
            <section class="relative">
                <div
                    class="pointer-events-none absolute -right-24 -top-24 h-56 w-56 rounded-full bg-violet-600/10 blur-3xl"
                    aria-hidden="true"
                ></div>

                <div class="relative flex items-center justify-between border-b border-white/[0.06] px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-violet-400/15 bg-violet-500/10">
                            <flux:icon.lock-closed class="size-4.5 text-violet-300" />
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <flux:heading size="lg">
                                    {{ __('Password') }}
                                </flux:heading>

                                <span class="hidden rounded-full border border-emerald-400/10 bg-emerald-400/[0.05] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-emerald-300/70 sm:inline-flex">
                                    {{ __('Protected') }}
                                </span>
                            </div>

                            <p class="mt-0.5 text-xs text-white">
                                {{ __('Update your account password.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative px-5 py-5 sm:px-6">
                    <form
                        method="POST"
                        wire:submit="updatePassword"
                        class="space-y-5"
                        novalidate
                    >
                        {{-- Current password --}}
                        <flux:input
                            wire:model="current_password"
                            :label="__('Current password')"
                            type="password"
                            required
                            autocomplete="current-password"
                            viewable
                            class="w-full [&_input]:min-h-12 [&_input]:rounded-xl [&_input]:px-4 [&_input]:text-sm"
                        />

                        {{-- New password / confirmation --}}
                        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                            <flux:input
                                wire:model="password"
                                :label="__('New password')"
                                type="password"
                                required
                                autocomplete="new-password"
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                viewable
                                class="w-full [&_input]:min-h-12 [&_input]:rounded-xl [&_input]:px-4 [&_input]:text-sm"
                            />

                            <flux:input
                                wire:model="password_confirmation"
                                :label="__('Confirm password')"
                                type="password"
                                required
                                autocomplete="new-password"
                                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                                viewable
                                class="w-full [&_input]:min-h-12 [&_input]:rounded-xl [&_input]:px-4 [&_input]:text-sm"
                            />
                        </div>

                        <div class="flex justify-end pt-1">
                            <flux:button
                                variant="primary"
                                type="submit"
                                data-test="update-password-button"
                                class="cursor-pointer"
                            >
                                {{ __('Save password') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- Two-factor --}}
            @if ($canManageTwoFactor)
                <section class="border-t border-white/[0.06]">
                    <div class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-fuchsia-400/15 bg-fuchsia-500/10">
                                <flux:icon.shield-check class="size-4.5 text-fuchsia-300" />
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:heading size="sm">
                                        {{ __('Two-factor authentication') }}
                                    </flux:heading>

                                    @if ($twoFactorEnabled)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/10 bg-emerald-400/[0.05] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-emerald-300/70">
                                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                                            {{ __('Enabled') }}
                                        </span>
                                    @else
                                        <span class="rounded-full border border-white/[0.07] bg-white/[0.025] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-white">
                                            {{ __('Not enabled') }}
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-0.5 truncate text-xs text-white">
                                    {{ __('Add an extra layer of protection to your account.') }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex shrink-0 flex-wrap items-center gap-2"
                            wire:cloak
                        >
                            @if ($twoFactorEnabled)
                                <flux:button
                                    variant="outline"
                                    wire:click="disable"
                                    class="cursor-pointer"
                                >
                                    {{ __('Disable 2FA') }}
                                </flux:button>

                                <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                            @else
                                <flux:modal.trigger name="two-factor-setup-modal">
                                    <flux:button
                                        variant="primary"
                                        wire:click="$dispatch('start-two-factor-setup')"
                                        class="cursor-pointer"
                                    >
                                        {{ __('Enable 2FA') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <livewire:pages::settings.two-factor-setup-modal
                                    :requires-confirmation="$requiresConfirmation"
                                />
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            {{-- Passkeys --}}
            @if ($canManagePasskeys)
                <section class="border-t border-white/[0.06]">
                    <div class="px-5 py-4 sm:px-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-purple-400/15 bg-purple-500/10">
                                    <flux:icon.key class="size-4.5 text-purple-300" />
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <flux:heading size="sm">
                                            {{ __('Passkeys') }}
                                        </flux:heading>

                                        <span class="rounded-full border border-white/[0.07] bg-white/[0.025] px-2 py-0.5 text-[9px] font-semibold tabular-nums text-white">
                                            {{ count($passkeys) }}
                                        </span>
                                    </div>

                                    <p class="mt-0.5 text-xs text-white">
                                        {{ __('Passwordless sign-in with your trusted devices.') }}
                                    </p>
                                </div>
                            </div>

                            <x-passkey-registration />
                        </div>

                        <div class="mt-4 overflow-hidden rounded-xl border border-white/[0.06] bg-black/15">
                            @forelse ($passkeys as $passkey)
                                <div
                                    class="flex items-center justify-between gap-3 px-3.5 py-3 transition hover:bg-white/[0.02] sm:px-4 {{ ! $loop->last ? 'border-b border-white/[0.05]' : '' }}"
                                >
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.035]">
                                            <flux:icon.key class="size-4 text-white" />
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate text-xs font-medium text-white">
                                                    {{ $passkey['name'] }}
                                                </p>

                                                @if ($passkey['authenticator'])
                                                    <flux:badge size="sm">
                                                        {{ $passkey['authenticator'] }}
                                                    </flux:badge>
                                                @endif
                                            </div>

                                            <p class="mt-0.5 text-[10px] text-white">
                                                {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}

                                                @if ($passkey['last_used_at_diff'])
                                                    <span class="mx-1 opacity-40">•</span>
                                                    {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        icon:variant="outline"
                                        wire:click="confirmDelete({{ $passkey['id'] }})"
                                        class="cursor-pointer text-red-400/50 hover:bg-red-500/[0.07] hover:text-red-300"
                                    />
                                </div>
                            @empty
                                <div class="flex items-center gap-3 px-4 py-5">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/[0.035]">
                                        <flux:icon.key class="size-4 text-white" />
                                    </div>

                                    <div>
                                        <p class="text-xs font-medium text-white">
                                            {{ __('No passkeys yet') }}
                                        </p>

                                        <p class="mt-0.5 text-[12px] text-white">
                                            {{ __('Add a passkey to sign in without a password.') }}
                                        </p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </x-pages::settings.layout>

    {{-- Delete passkey modal --}}
    <flux:modal
        name="delete-passkey-modal"
        class="max-w-md md:min-w-md"
        @close="closeDeleteModal"
        wire:model="showDeleteModal"
    >
        <div class="space-y-5">
            <div class="flex items-start gap-3.5">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-red-400/10 bg-red-500/[0.07]">
                    <flux:icon.trash class="size-5 text-red-300" />
                </div>

                <div class="space-y-1.5">
                    <flux:heading size="lg">
                        {{ __('Remove passkey') }}
                    </flux:heading>

                    <flux:text>
                        {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
                    </flux:text>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button
                    variant="outline"
                    wire:click="closeDeleteModal"
                    class="cursor-pointer"
                >
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    variant="danger"
                    wire:click="deletePasskey"
                    class="cursor-pointer"
                >
                    {{ __('Remove passkey') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
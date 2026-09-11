<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(
            EnableTwoFactorAuthentication::class
        );

        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception(
                    'Two-factor setup secret is not available.'
                );
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError(
                'setupData',
                'Failed to fetch setup data.'
            );

            $this->reset(
                'qrCodeSvg',
                'manualSetupKey'
            );
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(
        ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication
    ): void {
        $this->validate();

        $confirmTwoFactorAuthentication(
            auth()->user(),
            $this->code
        );

        $this->setupComplete = true;

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset(
            'code',
            'showVerificationStep'
        );

        $this->resetErrorBag();
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();
    }

    /**
     * Get the current modal configuration state.
     */
    #[Computed]
    public function modalConfig(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __(
                    'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app.'
                ),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __(
                    'Enter the 6-digit code from your authenticator app.'
                ),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __(
                'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'
            ),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<flux:modal
    name="two-factor-setup-modal"
    class="w-[calc(100vw-2rem)] max-w-lg md:min-w-[32rem]"
    @close="closeModal"
>
    <div class="overflow-hidden rounded-2xl border border-purple-500/15 bg-black shadow-2xl shadow-purple-950/30">

        {{-- Header --}}
        <div class="border-b border-purple-500/10 px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-purple-600/15 ring-1 ring-purple-500/20">
                    <flux:icon.shield-check class="size-5 text-purple-400" />
                </div>

                <div class="min-w-0">
                    <flux:heading
                        size="lg"
                        class="text-white"
                    >
                        {{ $this->modalConfig['title'] }}
                    </flux:heading>

                    <flux:text class="mt-1 text-sm text-zinc-200">
                        {{ $this->modalConfig['description'] }}
                    </flux:text>
                </div>
            </div>
        </div>

        @if ($showVerificationStep)

            {{-- Verification step --}}
            <div class="space-y-5 p-6">

                <div class="rounded-xl border border-purple-500/10 bg-purple-950/10 p-6">
                    <div
                        class="flex flex-col items-center justify-center"
                        x-data
                        x-init="$nextTick(() => $el.querySelector('input')?.focus())"
                    >
                        <div class="mb-5 text-center">
                            <div class="mx-auto mb-3 flex size-9 items-center justify-center rounded-lg bg-purple-600/15">
                                <flux:icon.key class="size-4 text-purple-400" />
                            </div>

                            <p class="text-sm font-medium text-white">
                                {{ __('Authentication code') }}
                            </p>

                            <p class="mt-1 text-xs text-zinc-500">
                                {{ __('Enter the 6-digit code from your authenticator app.') }}
                            </p>
                        </div>

                        <flux:otp
                            name="code"
                            wire:model="code"
                            length="6"
                            label="OTP Code"
                            label:sr-only
                            class="mx-auto"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <flux:button
                        variant="outline"
                        wire:click="resetVerification"
                        class="w-full cursor-pointer border-purple-500/15 bg-transparent text-zinc-400 hover:border-purple-500/30 hover:bg-purple-950/30 hover:text-white"
                    >
                        {{ __('Back') }}
                    </flux:button>

                    <flux:button
                        variant="primary"
                        wire:click="confirmTwoFactor"
                        x-bind:disabled="$wire.code.length < 6"
                        class="w-full cursor-pointer bg-purple-600 hover:bg-purple-500"
                    >
                        {{ __('Confirm') }}
                    </flux:button>
                </div>

            </div>

        @else

            {{-- Setup step --}}
            <div class="space-y-5 p-6">

                @error('setupData')
                    <flux:callout
                        variant="danger"
                        icon="x-circle"
                        heading="{{ $message }}"
                    />
                @enderror

                {{-- QR code --}}
                <div class="flex items-center gap-5 rounded-xl border border-purple-500/10 bg-purple-950/10 p-4">

                    <div class="relative flex size-36 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                        @empty($qrCodeSvg)

                            <flux:icon.loading class="text-zinc-500" />

                        @else

                            <div
                                x-data
                                class="flex size-full items-center justify-center p-3"
                            >
                                <div
                                    class="rounded bg-white p-2"
                                    :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ? 'filter: invert(1) brightness(1.5)' : ''"
                                >
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>

                        @endempty
                    </div>

                    <div class="min-w-0">
                        <div class="mb-2 flex items-center gap-2">
                            <flux:icon.qr-code class="size-4 text-purple-400" />

                            <span class="text-xs font-semibold uppercase tracking-wider text-purple-400">
                                {{ __('Scan QR code') }}
                            </span>
                        </div>

                        <p class="text-sm leading-5 text-zinc-300">
                            {{ __('Open your authenticator app and scan the QR code to connect your account.') }}
                        </p>

                        <p class="mt-2 text-xs leading-5 text-zinc-100">
                            {{ __('You can also enter the setup key manually.') }}
                        </p>
                    </div>
                </div>

                {{-- Continue --}}
                <flux:button
                    :disabled="$errors->has('setupData')"
                    variant="primary"
                    wire:click="showVerificationIfNecessary"
                    class="w-full cursor-pointer bg-purple-600 hover:bg-purple-500"
                >
                    {{ $this->modalConfig['buttonText'] }}
                </flux:button>

                {{-- Divider --}}
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-purple-500/10"></div>

                    <span class="text-[11px] font-medium uppercase tracking-wider text-zinc-300">
                        {{ __('Manual setup') }}
                    </span>

                    <div class="h-px flex-1 bg-purple-500/10"></div>
                </div>

                {{-- Manual setup key --}}
                <div
                    x-data="{
                        copied: false,
                        async copy() {
                            try {
                                await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                this.copied = true;
                                setTimeout(() => this.copied = false, 1500);
                            } catch (e) {
                                console.warn('Could not copy to clipboard');
                            }
                        }
                    }"
                    class="flex overflow-hidden rounded-xl border border-purple-500/10 bg-purple-950/10"
                >
                    @empty($manualSetupKey)

                        <div class="flex h-12 w-full items-center justify-center">
                            <flux:icon.loading
                                variant="mini"
                                class="text-purple-400"
                            />
                        </div>

                    @else

                        <input
                            type="text"
                            readonly
                            value="{{ $manualSetupKey }}"
                            class="h-12 min-w-0 flex-1 border-0 bg-transparent px-4 font-mono text-sm tracking-wider text-purple-200 outline-none focus:ring-0"
                        />

                        <button
                            type="button"
                            @click="copy()"
                            class="flex size-12 shrink-0 cursor-pointer items-center justify-center border-l border-purple-500/10 text-purple-400 transition hover:bg-purple-600/10 hover:text-purple-300"
                            aria-label="{{ __('Copy setup key') }}"
                        >
                            <flux:icon.document-duplicate
                                x-show="!copied"
                                variant="outline"
                                class="size-4"
                            />

                            <flux:icon.check
                                x-show="copied"
                                variant="solid"
                                class="size-4 text-purple-300"
                            />
                        </button>

                    @endempty
                </div>

            </div>

        @endif
    </div>
</flux:modal>
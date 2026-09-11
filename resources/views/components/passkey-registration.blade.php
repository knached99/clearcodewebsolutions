@assets
    @vite('resources/js/passkeys.js')
@endassets

<div
    x-data="{
        supported: false,
        showForm: false,
        name: '',
        loading: false,
        error: null,

        updateSupport() {
            this.supported = Boolean(window.Passkeys?.isSupported());
        },

        getDefaultPasskeyName() {
            const ua = navigator.userAgent;

            const browser = [
                { pattern: /Edg|Edge/, name: 'Edge' },
                { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
                { pattern: /Firefox|FxiOS/, name: 'Firefox' },
                { pattern: /Chrome|CriOS/, name: 'Chrome' },
                { pattern: /Safari/, name: 'Safari' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            const os = [
                { pattern: /iPhone/, name: 'iPhone' },
                { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
                { pattern: /Android/, name: 'Android' },
                { pattern: /Mac/, name: 'Mac' },
                { pattern: /Windows/, name: 'Windows' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            return [browser, os].filter(Boolean).join(' on ') || '';
        },

        init() {
            this.name = this.getDefaultPasskeyName();
            this.updateSupport();

            window.addEventListener(
                'passkeys:ready',
                () => this.updateSupport(),
                { once: true }
            );
        },

        async register() {
            if (!this.name.trim()) return;

            this.loading = true;
            this.error = null;

            try {
                await window.Passkeys.register({
                    name: this.name,
                });

                this.name = '';
                this.showForm = false;

                await $wire.loadPasskeys();
            } catch (e) {
                if (e.constructor?.name !== 'UserCancelledError') {
                    this.error = e.message;
                }
            } finally {
                this.loading = false;
            }
        },

        cancel() {
            this.showForm = false;
            this.name = '';
            this.error = null;
        },
    }"
>
    {{-- Unsupported browser --}}
    <template x-if="!supported">
        <div class="flex items-center gap-3 rounded-xl border border-purple-500/10 bg-purple-950/10 px-4 py-3">
            <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-purple-600/10">
                <flux:icon.information-circle class="size-4 text-purple-400" />
            </div>

            <flux:text class="text-sm text-zinc-200">
                {{ __('Passkeys are not supported in this browser.') }}
            </flux:text>
        </div>
    </template>

    {{-- Add button --}}
    <template x-if="supported && !showForm">
        <div>
            <flux:button
                variant="primary"
                icon="plus"
                x-on:click="showForm = true"
                class="cursor-pointer bg-purple-600 hover:bg-purple-500"
            >
                {{ __('Add passkey') }}
            </flux:button>
        </div>
    </template>

    {{-- Registration form --}}
    <template x-if="supported && showForm">
        <div class="overflow-hidden rounded-xl border border-purple-500/10 bg-purple-950/10">

            {{-- Form header --}}
            <div class="flex items-center gap-3 border-b border-purple-500/10 px-4 py-3">
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-purple-600/15">
                    <flux:icon.key class="size-4 text-purple-400" />
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-medium text-white">
                        {{ __('New passkey') }}
                    </p>

                    <p class="text-sm text-zinc-200">
                        {{ __('Choose a name so you can recognize this device later.') }}
                    </p>
                </div>
            </div>

            {{-- Form body --}}
            <div class="space-y-4 p-4">

                <div>
                    <flux:input
                        label="{{ __('Passkey name') }}"
                        x-model="name"
                        placeholder="{{ __('e.g., MacBook Pro, iPhone') }}"
                        x-on:keydown.enter.prevent="register()"
                        x-ref="passkeyNameInput"
                        x-init="$nextTick(() => $refs.passkeyNameInput?.focus())"
                        class="w-full [&_input]:min-h-11 [&_input]:rounded-xl"
                    />

                    <flux:text class="!mt-1 text-sm text-zinc-200">
                        {{ __('Use a name that makes this passkey easy to identify.') }}
                    </flux:text>
                </div>

                {{-- Error --}}
                <p
                    x-show="error"
                    x-text="error"
                    x-cloak
                    class="rounded-lg border border-purple-500/15 bg-purple-950/20 px-3 py-2 text-sm text-purple-300"
                ></p>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2">
                    <flux:button
                        variant="ghost"
                        x-on:click="cancel()"
                        class="cursor-pointer text-zinc-200 hover:bg-purple-950/30 hover:text-white"
                    >
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button
                        variant="primary"
                        x-on:click="register()"
                        x-bind:disabled="loading || !name.trim()"
                        class="cursor-pointer bg-purple-600 hover:bg-purple-500"
                    >
                        <span x-show="!loading">
                            {{ __('Register passkey') }}
                        </span>

                        <span x-show="loading" x-cloak>
                            {{ __('Registering...') }}
                        </span>
                    </flux:button>
                </div>
            </div>
        </div>
    </template>
</div>
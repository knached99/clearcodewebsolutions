<x-layouts::auth :title="__('Forgot password')">

    <div class="flex flex-col gap-7">

        {{-- Header --}}
        <div>
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-purple-400/15 bg-purple-500/8 px-3 py-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-purple-400 shadow-[0_0_12px_rgba(168,85,247,.9)]"></span>

                <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-purple-300/80">
                    {{ __('Account recovery') }}
                </span>
            </div>

            <h1 class="text-3xl font-semibold tracking-[-0.04em] text-white sm:text-[2.15rem]">
                {{ __('Forgot password?') }}
            </h1>

            <p class="mt-2 max-w-sm text-sm leading-6 text-white/40">
                {{ __('Enter your email and we’ll send you a secure link to reset your password.') }}
            </p>
        </div>

        {{-- Session Status --}}
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        {{-- Form --}}
        <form
            method="POST"
            action="{{ route('password.email') }}"
            class="flex flex-col gap-5"
            novalidate
        >
            @csrf

            {{-- Email --}}
            <div class="auth-field">
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    :error="$errors->first('email')"
                    type="email"
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                />
            </div>

            {{-- Submit --}}
            <div class="pt-1">
                <button
                    type="submit"
                    class="group relative flex h-12 w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 text-sm font-semibold text-white shadow-lg shadow-purple-950/40 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/40 focus:outline-none focus:ring-2 focus:ring-purple-400/50 active:translate-y-0"
                    data-test="email-password-reset-link-button"
                >
                    <span
                        class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                    ></span>

                    <span class="relative flex items-center gap-2">
                        {{ __('Email password reset link') }}

                        <svg
                            class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"
                            viewBox="0 0 20 20"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 10h11M11 6l4 4-4 4"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                </button>
            </div>
        </form>

        {{-- Back to Login --}}
        <div class="flex items-center justify-center gap-1 border-t border-white/6 pt-5 text-sm">
            <span class="text-white/30">
                {{ __('Remember your password?') }}
            </span>

            <flux:link
                :href="route('login')"
                wire:navigate
                class="cursor-pointer font-medium text-purple-300/80 transition hover:text-purple-200"
            >
                {{ __('Log in') }}
            </flux:link>
        </div>

        {{-- Security note --}}
        <div class="flex items-center justify-center gap-2">
            <svg
                class="h-3.5 w-3.5 text-purple-300/40"
                viewBox="0 0 24 24"
                fill="none"
                aria-hidden="true"
            >
                <path
                    d="M12 3 5 6v5c0 4.5 2.9 8.5 7 10 4.1-1.5 7-5.5 7-10V6l-7-3Z"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                />
                <path
                    d="m9 12 2 2 4-4"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>

            <span class="text-[11px] text-white/20">
                {{ __('Your information is securely protected') }}
            </span>
        </div>

    </div>

</x-layouts::auth>
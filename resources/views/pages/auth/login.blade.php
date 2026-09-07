<x-layouts::auth :title="__('Log in')">

    <div class="flex flex-col gap-7">

        {{-- Header --}}
    <div>
        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-purple-400/15 bg-purple-500/8 px-3 py-1.5">
            <span class="h-1.5 w-1.5 rounded-full bg-purple-400 shadow-[0_0_12px_rgba(168,85,247,.9)]"></span>

            <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-purple-300/80">
                {{ __('Secure sign in') }}
            </span>
        </div>

        <h1 class="text-3xl font-semibold tracking-[-0.04em] text-white sm:text-[2.15rem]">
            {{ __('Welcome back') }}
        </h1>

        <p class="mt-2 max-w-sm text-sm leading-6 text-white/40">
            {{ __('Log in to continue to your account.') }}
        </p>
    </div>

        {{-- Session Status --}}
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        {{-- Team Invitation --}}
        @if ($teamInvitation)
            <x-team-invitation-alert
                :invitation="$teamInvitation"
                :action="__('Log in')"
            />
        @endif

        {{-- Passkey --}}
        <x-passkey-verify />

        {{-- Login Form --}}
        <form
            method="POST"
            action="{{ route('login.store') }}"
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

            {{-- Password --}}
            <div class="auth-field">
                <div class="relative">
                    <flux:input
                        name="password"
                        :label="__('Password')"
                        :error="$errors->first('password')"
                        type="password"
                        autocomplete="current-password"
                        :placeholder="__('Enter your password')"
                        viewable
                    />

                    @if (Route::has('password.request'))
                        <flux:link
                            class="absolute top-0 cursor-pointer text-xs font-medium text-purple-300/80 transition hover:text-purple-200 end-0"
                            :href="route('password.request')"
                            wire:navigate
                        >
                            {{ __('Forgot password?') }}
                        </flux:link>
                    @endif
                </div>
            </div>

            {{-- Remember --}}
            <div class="flex items-center justify-between">
                <flux:checkbox
                    name="remember"
                    :label="__('Remember me')"
                    :checked="old('remember')"
                />
            </div>

            {{-- Submit --}}
            <div class="pt-1">
                <button
                    type="submit"
                    data-test="login-button"
                    class="group relative flex h-12 w-full cursor-pointer items-center justify-center overflow-hidden rounded-xl border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 text-sm font-semibold text-white shadow-lg shadow-purple-950/40 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/40 focus:outline-none focus:ring-2 focus:ring-purple-400/50 active:translate-y-0"
                >
                    <span
                        class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                    ></span>

                    <span class="relative flex items-center gap-2">
                        {{ __('Log in') }}

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

        {{-- Security note --}}
        <div class="flex items-center justify-center gap-2 border-t border-white/6 pt-1">
            <svg
                class="h-3.5 w-3.5 text-purple-300/45"
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

            <span class="text-[11px] text-white/25">
                {{ __('Your connection is protected') }}
            </span>
        </div>

    </div>

</x-layouts::auth>
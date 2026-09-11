<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate(
            $this->profileRules($user->id)
        );

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(
            variant: 'success',
            text: __('Profile updated.')
        );
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(
                default: route(
                    'dashboard',
                    absolute: false
                )
            );

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash(
            'status',
            'verification-link-sent'
        );
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail
            && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (
                Auth::user() instanceof MustVerifyEmail
                && Auth::user()->hasVerifiedEmail()
            );
    }
};
?>

<section class="w-full">

    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Profile settings') }}
    </flux:heading>

    <x-pages::settings.layout
        :heading="__('Profile')"
        :subheading="__('Manage your personal information and account identity.')"
    >

        {{-- ================================================================
            PROFILE
        ================================================================= --}}
        <div
            class="relative mt-8 overflow-hidden rounded-[1.75rem] border border-white/8 bg-[#09070f] shadow-2xl shadow-black/20"
        >

            {{-- Background glow --}}
            <div
                class="pointer-events-none absolute -left-32 -top-32 h-72 w-72 rounded-full bg-purple-600/12 blur-[110px]"
                aria-hidden="true"
            ></div>

            <div
                class="pointer-events-none absolute -right-32 -top-20 h-64 w-64 rounded-full bg-fuchsia-600/8 blur-[110px]"
                aria-hidden="true"
            ></div>


            {{-- ============================================================
                COMPACT PROFILE HEADER
            ============================================================= --}}
            <div class="relative px-6 py-6 sm:px-8">

                <div class="flex items-center justify-between gap-6">

                    {{-- Identity --}}
                    <div class="flex min-w-0 items-center gap-4">

                        {{-- Avatar --}}
                        <div class="relative shrink-0">

                            <div
                                class="absolute -inset-1 rounded-2xl bg-linear-to-br from-purple-500/30 via-violet-500/10 to-fuchsia-500/25 blur-lg"
                            ></div>

                            <div
                                class="relative flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-linear-to-br from-purple-600 via-violet-600 to-fuchsia-600 text-lg font-semibold text-white shadow-xl shadow-purple-950/40"
                            >
                                {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
                            </div>

                            <span
                                class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-[3px] border-[#09070f] bg-emerald-400"
                            ></span>

                        </div>


                        {{-- Name / email --}}
                        <div class="min-w-0">

                            <div class="flex min-w-0 flex-wrap items-center gap-2">

                                <h3 class="max-w-[min(60vw,420px)] truncate text-lg font-semibold tracking-tight text-white sm:text-xl">
                                    {{ $name ?: __('Your profile') }}
                                </h3>

                                @if (! $this->hasUnverifiedEmail)

                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-400/15 bg-emerald-400/8 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-emerald-300/80">

                                        <span class="h-1 w-1 rounded-full bg-emerald-400"></span>

                                        {{ __('Verified') }}

                                    </span>

                                @endif

                            </div>

                            <p class="mt-0.5 max-w-[min(65vw,520px)] truncate text-sm text-white">
                                {{ $email }}
                            </p>

                        </div>

                    </div>


                    {{-- Status --}}
                    <div class="hidden shrink-0 items-center gap-2.5 sm:flex">

                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-white/7 bg-white/[0.025]">
                            <svg
                                class="h-3.5 w-3.5 text-purple-300/55"
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
                        </div>

                        <div>
                            <p class="text-[9px] uppercase tracking-[0.16em] text-white">
                                {{ __('Account') }}
                            </p>

                            <p class="text-[11px] font-medium text-white">
                                {{ __('Protected') }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================
                FORM
            ============================================================= --}}
            <form
                wire:submit="updateProfileInformation"
                class="relative"
                novalidate
            >

                {{-- Fields --}}
                <div class="border-t border-white/7 px-6 py-6 sm:px-8">

                    <div class="mb-5 flex items-end justify-between gap-4">

                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-purple-300/50">
                                {{ __('Personal details') }}
                            </p>

                            <h4 class="mt-1 text-sm font-semibold text-white">
                                {{ __('Account information') }}
                            </h4>
                        </div>

                        <p class="hidden text-right text-[10px] text-white sm:block">
                            {{ __('Changes apply immediately') }}
                        </p>

                    </div>


                    {{-- Large fields --}}
                    <div class="grid gap-4 lg:grid-cols">

                        {{-- Name --}}
                        <div
                            class="min-w-0 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5 transition duration-200 focus-within:border-purple-400/25 focus-within:bg-white/[0.035]"
                        >

                            <flux:input
                                wire:model="name"
                                :label="__('Full name')"
                                type="text"
                                autocomplete="name"
                                autofocus
                                :error="$errors->first('name')"
                            />

                        </div>


                        {{-- Email --}}
                        <div
                            class="min-w-0 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5 transition duration-200 focus-within:border-purple-400/25 focus-within:bg-white/[0.035]"
                        >

                            <flux:input
                                wire:model="email"
                                :label="__('Email address')"
                                type="email"
                                autocomplete="email"
                                :error="$errors->first('email')"
                            />

                        </div>

                    </div>


                    {{-- ====================================================
                        VERIFICATION
                    ===================================================== --}}
                    @if ($this->hasUnverifiedEmail)

                        <div class="mt-4">

                            <div class="flex flex-col gap-4 rounded-xl border border-amber-300/10 bg-amber-300/[0.035] p-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-amber-300/10 bg-amber-400/8">

                                        <svg
                                            class="h-4 w-4 text-amber-300/75"
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
                                                d="M12 8v4"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                            />
                                            <circle
                                                cx="12"
                                                cy="15.5"
                                                r=".75"
                                                fill="currentColor"
                                            />
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="text-xs font-semibold text-white">
                                            {{ __('Email verification required') }}
                                        </p>

                                        <p class="mt-0.5 truncate text-[11px] text-white">
                                            {{ __('Verify your email to fully protect your account.') }}
                                        </p>

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    wire:click.prevent="resendVerificationNotification"
                                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-amber-300/10 bg-amber-300/8 px-4 text-xs font-semibold text-amber-200/80 transition hover:border-amber-300/20 hover:bg-amber-300/12 hover:text-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-300/20"
                                >
                                    {{ __('Send verification') }}
                                </button>

                            </div>


                            @if (session('status') === 'verification-link-sent')

                                <div class="mt-2 flex items-center gap-2 px-1">

                                    <svg
                                        class="h-3.5 w-3.5 text-emerald-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="m5 12 4 4L19 6"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    <span class="text-[11px] text-emerald-300/70">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </span>

                                </div>

                            @endif

                        </div>

                    @endif

                </div>


                {{-- ========================================================
                    SAVE BAR
                ========================================================= --}}
                <div class="flex items-center justify-between gap-4 border-t border-white/7 bg-white/[0.012] px-6 py-4 sm:px-8">

                    <div class="hidden items-center gap-2 sm:flex">

                        <span class="h-1.5 w-1.5 rounded-full bg-purple-400/50"></span>

                        <span class="text-[10px] text-white">
                            {{ __('Your information is securely stored.') }}
                        </span>

                    </div>

                    <button
                        type="submit"
                        class="group relative inline-flex h-10 w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-6 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/50 active:translate-y-0 sm:w-auto"
                        data-test="update-profile-button"
                    >

                        <span
                            class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                        ></span>

                        <span class="relative flex items-center gap-2">

                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M5 12.5 9.5 17 19 7"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            {{ __('Save changes') }}

                        </span>

                    </button>

                </div>

            </form>

        </div>


        {{-- ================================================================
            DELETE ACCOUNT
        ================================================================= --}}
        @if ($this->showDeleteUser)

            <div
                class="relative mt-6 overflow-hidden rounded-[1.5rem] border border-red-400/10 bg-[#09070f]"
            >

                <div
                    class="pointer-events-none absolute right-0 top-0 h-40 w-40 rounded-full bg-red-500/[0.025] blur-[70px]"
                    aria-hidden="true"
                ></div>


                <div class="relative flex flex-col gap-5 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-400/10 bg-red-500/8">

                            <svg
                                class="h-4 w-4 text-red-300/65"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                        </div>

                        <div>
                            <p class="text-xs font-semibold text-white">
                                {{ __('Delete account') }}
                            </p>

                            <p class="mt-0.5 text-[11px] text-white">
                                {{ __('Permanently remove your account and data.') }}
                            </p>
                        </div>

                    </div>


                    <div class="sm:max-w-[55%]">
                        <livewire:pages::settings.delete-user-form />
                    </div>

                </div>

            </div>

        @endif

    </x-pages::settings.layout>

</section>
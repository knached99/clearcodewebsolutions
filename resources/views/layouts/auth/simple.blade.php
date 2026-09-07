<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen overflow-x-hidden bg-[#05030a] text-white antialiased">
        <div class="relative min-h-svh overflow-hidden">

            {{-- ============================================================
                BACKGROUND IMAGE
            ============================================================= --}}
            <div
                class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                style="background-image: url('https://images.unsplash.com/photo-1519608487953-e999c86e7455?auto=format&fit=crop&w=2400&q=85');"
                aria-hidden="true"
            ></div>

            {{-- Dark cinematic overlay --}}
            <div
                class="absolute inset-0 bg-[#05030a]/75"
                aria-hidden="true"
            ></div>

            {{-- Purple atmospheric overlay --}}
            <div
                class="absolute inset-0 bg-linear-to-br from-purple-950/75 via-[#08050f]/70 to-black/85"
                aria-hidden="true"
            ></div>

            {{-- Right-side darkness behind login --}}
            <div
                class="absolute inset-y-0 right-0 w-full lg:w-[58%] bg-linear-to-l from-[#05030a]/95 via-[#05030a]/80 to-transparent"
                aria-hidden="true"
            ></div>

            {{-- Purple glow --}}
            <div
                class="pointer-events-none absolute -left-32 top-1/4 h-[36rem] w-[36rem] rounded-full bg-purple-600/15 blur-[140px]"
                aria-hidden="true"
            ></div>

            <div
                class="pointer-events-none absolute bottom-[-15rem] right-[-10rem] h-[38rem] w-[38rem] rounded-full bg-fuchsia-600/10 blur-[150px]"
                aria-hidden="true"
            ></div>

            {{-- Subtle grid --}}
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.025]"
                style="
                    background-image:
                        linear-gradient(rgba(255,255,255,.8) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,255,255,.8) 1px, transparent 1px);
                    background-size: 64px 64px;
                "
                aria-hidden="true"
            ></div>

            {{-- ============================================================
                CONTENT
            ============================================================= --}}
            <div class="relative z-10 min-h-svh">

                <div class="mx-auto flex min-h-svh w-full max-w-[1500px]">

                    {{-- ====================================================
                        LEFT SIDE
                    ===================================================== --}}
                    <section
                        class="hidden flex-1 items-center px-10 py-16 lg:flex xl:px-20"
                    >
                        <div class="max-w-xl">

                            {{-- Brand --}}
                            <a
                                href="{{ route('home') }}"
                                class="group mb-12 inline-flex items-center gap-4"
                                wire:navigate
                            >
                                <div
                                    class="relative flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.06] shadow-2xl shadow-purple-950/50 backdrop-blur-xl transition duration-300 group-hover:border-purple-300/30 group-hover:bg-white/[0.09]"
                                >
                                    <div
                                        class="absolute inset-0 rounded-2xl bg-purple-500/15 blur-xl transition duration-300 group-hover:bg-purple-500/25"
                                    ></div>

                                    <x-app-logo-icon
                                        class="relative size-7 fill-current text-white"
                                    />
                                </div>

                                <div>
                                    <div class="text-lg font-semibold tracking-tight text-white">
                                        {{ config('app.name', 'ClearCode') }}
                                    </div>

                                    <div class="text-[10px] font-medium uppercase tracking-[0.28em] text-purple-300/55">
                                        Developer Support
                                    </div>
                                </div>
                            </a>

                            {{-- Eyebrow --}}
                            <div class="mb-6 flex items-center gap-3">
                                <span class="h-px w-10 bg-purple-400/50"></span>

                                <span class="text-[10px] font-semibold uppercase tracking-[0.32em] text-purple-300/75">
                                    Welcome back
                                </span>
                            </div>

                            {{-- Main heading --}}
                            <h1 class="text-5xl font-semibold leading-[1.04] tracking-[-0.045em] text-white xl:text-6xl">
                                Build better.
                                <span class="block bg-linear-to-r from-purple-200 via-violet-300 to-fuchsia-300 bg-clip-text text-transparent">
                                    Ship faster.
                                </span>
                            </h1>

                            <p class="mt-7 max-w-lg text-base leading-8 text-white/45">
                                Your development support workspace is waiting.
                                Pick up where you left off, manage your requests,
                                and keep your projects moving forward.
                            </p>

                            {{-- Feature list --}}
                            <div class="mt-10 space-y-4">

                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-purple-300/10 bg-purple-500/10"
                                    >
                                        <svg
                                            class="h-4 w-4 text-purple-300"
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
                                        <p class="text-sm font-medium text-white/80">
                                            Secure by design
                                        </p>

                                        <p class="mt-0.5 text-xs text-white/35">
                                            Your account and data stay protected.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-purple-300/10 bg-purple-500/10"
                                    >
                                        <svg
                                            class="h-4 w-4 text-purple-300"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 4v-4H6.5A2.5 2.5 0 0 1 4 13.5v-7Z"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-white/80">
                                            Everything in one place
                                        </p>

                                        <p class="mt-0.5 text-xs text-white/35">
                                            Keep conversations, tickets, and updates together.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-purple-300/10 bg-purple-500/10"
                                    >
                                        <svg
                                            class="h-4 w-4 text-purple-300"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M4 12h4l2-7 4 14 2-7h4"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-white/80">
                                            Stay in motion
                                        </p>

                                        <p class="mt-0.5 text-xs text-white/35">
                                            See what needs your attention at a glance.
                                        </p>
                                    </div>
                                </div>

                            </div>

                            {{-- Bottom decorative line --}}
                            <div class="mt-14 flex items-center gap-4">
                                <div class="h-px w-20 bg-linear-to-r from-purple-400/50 to-transparent"></div>

                                <span class="text-[10px] uppercase tracking-[0.25em] text-white/20">
                                    {{ config('app.name', 'ClearCode') }}
                                </span>
                            </div>

                        </div>
                    </section>


                    {{-- ====================================================
                        RIGHT SIDE — LOGIN AREA
                    ===================================================== --}}
                    <section
                        class="flex w-full items-center justify-center px-5 py-10 sm:px-8 lg:w-[52%] lg:justify-end lg:px-12 xl:px-20"
                    >
                        <div class="w-full max-w-[440px]">

                            {{-- Mobile brand --}}
                            <div class="mb-8 flex justify-center lg:hidden">
                                <a
                                    href="{{ route('home') }}"
                                    class="group inline-flex items-center gap-3"
                                    wire:navigate
                                >
                                    <div
                                        class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.06] backdrop-blur-xl"
                                    >
                                        <div
                                            class="absolute inset-0 rounded-2xl bg-purple-500/15 blur-xl"
                                        ></div>

                                        <x-app-logo-icon
                                            class="relative size-6 fill-current text-white"
                                        />
                                    </div>

                                    <span class="text-base font-semibold text-white">
                                        {{ config('app.name', 'ClearCode') }}
                                    </span>
                                </a>
                            </div>

                            {{-- Login panel --}}
                            <div
                                class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-black/35 p-6 shadow-2xl shadow-black/60 backdrop-blur-2xl sm:p-9"
                            >

                                {{-- Top glow --}}
                                <div
                                    class="pointer-events-none absolute inset-x-10 top-0 h-px bg-linear-to-r from-transparent via-purple-300/70 to-transparent"
                                ></div>

                                <div
                                    class="pointer-events-none absolute -right-24 -top-24 h-56 w-56 rounded-full bg-purple-500/10 blur-3xl"
                                ></div>

                                <div
                                    class="pointer-events-none absolute -bottom-32 -left-24 h-48 w-48 rounded-full bg-fuchsia-500/5 blur-3xl"
                                ></div>

                                <div class="relative">
                                    {{ $slot }}
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="mt-6 flex items-center justify-center gap-2">
                                <svg
                                    class="h-3.5 w-3.5 text-purple-300/35"
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
                                </svg>

                                <span class="text-[11px] text-white/25">
                                    Secure access to your account
                                </span>
                            </div>

                        </div>
                    </section>

                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
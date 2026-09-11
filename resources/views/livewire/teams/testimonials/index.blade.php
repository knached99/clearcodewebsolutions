<div
    x-data
    x-init="
        @if ($highlightedTestimonialId)
            setTimeout(() => {
                const target = document.getElementById(
                    'testimonial-{{ $highlightedTestimonialId }}'
                );

                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }, 400);
        @endif
    "
    class="cc-admin-testimonials relative min-h-screen overflow-hidden text-white"
>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* =========================================================
           PAGE
        ========================================================= */

        .cc-admin-testimonials {
            --lime: #ADD322;
            --purple: #8b5cf6;
            --violet: #6d28d9;
            --bg: #050408;
            --panel: #0d0b13;

            background:
                radial-gradient(
                    900px circle at 8% -5%,
                    rgba(139, 92, 246, .20),
                    transparent 55%
                ),
                radial-gradient(
                    700px circle at 92% 4%,
                    rgba(109, 40, 217, .16),
                    transparent 52%
                ),
                radial-gradient(
                    650px circle at 52% 45%,
                    rgba(91, 33, 182, .075),
                    transparent 65%
                ),
                radial-gradient(
                    500px circle at 15% 100%,
                    rgba(173, 211, 34, .045),
                    transparent 60%
                ),
                linear-gradient(
                    135deg,
                    #030205 0%,
                    #08060d 30%,
                    #0a0711 52%,
                    #06050a 78%,
                    #030305 100%
                );

            isolation: isolate;
        }

        .cc-admin-testimonials .noise {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;

            background:
                radial-gradient(
                    550px circle at 25% 18%,
                    rgba(139, 92, 246, .12),
                    transparent 70%
                ),
                radial-gradient(
                    450px circle at 80% 28%,
                    rgba(173, 211, 34, .035),
                    transparent 70%
                ),
                linear-gradient(
                    115deg,
                    transparent 0%,
                    rgba(255,255,255,.012) 45%,
                    transparent 70%
                );
        }

        /* =========================================================
           GLASS
        ========================================================= */

        .cc-admin-testimonials .glass {
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.045),
                    rgba(255,255,255,.018)
                );

            border: 1px solid rgba(255,255,255,.075);

            box-shadow:
                0 25px 80px rgba(0,0,0,.28),
                inset 0 1px 0 rgba(255,255,255,.035);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* =========================================================
           TESTIMONIAL CARD
        ========================================================= */

        .cc-admin-testimonials .testimonial-card {
            position: relative;

            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.045),
                    rgba(255,255,255,.012)
                );

            border: 1px solid rgba(255,255,255,.075);

            box-shadow:
                0 25px 70px rgba(0,0,0,.28),
                inset 0 1px 0 rgba(255,255,255,.025);

            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);

            transition:
                transform .35s ease,
                border-color .35s ease,
                box-shadow .35s ease;
        }

        .cc-admin-testimonials .testimonial-card:hover {
            transform: translateY(-5px);

            border-color: rgba(173,211,34,.22);

            box-shadow:
                0 35px 90px rgba(0,0,0,.42),
                0 0 0 1px rgba(173,211,34,.025),
                0 0 50px rgba(139,92,246,.035);
        }

        /*
        |--------------------------------------------------------------------------
        | URL HIGHLIGHT
        |--------------------------------------------------------------------------
        */

        .cc-admin-testimonials .testimonial-card.is-highlighted {
            border-color: rgba(173,211,34,.80);

            box-shadow:
                0 0 0 1px rgba(173,211,34,.45),
                0 0 25px rgba(173,211,34,.30),
                0 0 70px rgba(173,211,34,.18),
                0 35px 100px rgba(0,0,0,.50),
                inset 0 1px 0 rgba(173,211,34,.16);

            animation:
                testimonial-highlight 1.8s ease-in-out infinite;
        }

        .cc-admin-testimonials .testimonial-card.is-highlighted::before {
            content: "";

            position: absolute;
            inset: -1px;

            border-radius: 1.5rem;

            pointer-events: none;

            background:
                radial-gradient(
                    500px circle at 50% 50%,
                    rgba(173,211,34,.10),
                    transparent 65%
                );

            opacity: 1;

            z-index: -1;
        }

        @keyframes testimonial-highlight {
            0%,
            100% {
                box-shadow:
                    0 0 0 1px rgba(173,211,34,.40),
                    0 0 22px rgba(173,211,34,.22),
                    0 0 60px rgba(173,211,34,.12),
                    0 35px 100px rgba(0,0,0,.50),
                    inset 0 1px 0 rgba(173,211,34,.12);
            }

            50% {
                box-shadow:
                    0 0 0 1px rgba(173,211,34,.70),
                    0 0 35px rgba(173,211,34,.42),
                    0 0 95px rgba(173,211,34,.22),
                    0 35px 110px rgba(0,0,0,.55),
                    inset 0 1px 0 rgba(173,211,34,.20);
            }
        }

        /* =========================================================
           AVATAR
        ========================================================= */

        .cc-admin-testimonials .avatar {
            background:
                linear-gradient(
                    145deg,
                    rgba(173,211,34,.14),
                    rgba(139,92,246,.16)
                );

            border: 1px solid rgba(173,211,34,.18);

            box-shadow:
                0 12px 35px rgba(0,0,0,.25),
                inset 0 1px 0 rgba(255,255,255,.05);
        }

        /* =========================================================
           ACTIONS
        ========================================================= */

        .cc-admin-testimonials .action-button {
            transition:
                background .2s ease,
                color .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .cc-admin-testimonials .action-button:hover {
            transform: translateY(-1px);
        }

        /* =========================================================
           INPUTS
        ========================================================= */

        .cc-admin-testimonials .admin-input {
            background:
                linear-gradient(
                    180deg,
                    rgba(255,255,255,.028),
                    rgba(255,255,255,.012)
                );

            border: 1px solid rgba(255,255,255,.075);
            color: white;

            transition:
                border-color .25s ease,
                background .25s ease,
                box-shadow .25s ease;
        }

        .cc-admin-testimonials .admin-input:hover {
            border-color: rgba(255,255,255,.13);
            background: rgba(255,255,255,.035);
        }

        .cc-admin-testimonials .admin-input:focus {
            border-color: rgba(173,211,34,.45);
            background: rgba(0,0,0,.28);

            box-shadow:
                0 0 0 3px rgba(173,211,34,.045),
                0 10px 35px rgba(0,0,0,.15);

            outline: none;
        }

        .cc-admin-testimonials .admin-input::placeholder {
            color: rgba(255,255,255,.18);
        }

        /* =========================================================
           STATUS
        ========================================================= */

        .cc-admin-testimonials .status-dot {
            box-shadow: 0 0 14px currentColor;
        }

        /* =========================================================
           FILTERS
        ========================================================= */

        .cc-admin-testimonials .filter-button {
            transition:
                color .2s ease,
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .cc-admin-testimonials .filter-button:hover {
            transform: translateY(-1px);
        }

        /* =========================================================
           MODAL
        ========================================================= */

        .cc-admin-testimonials .cc-modal-backdrop {
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(139,92,246,.14),
                    transparent 35%
                ),
                rgba(0,0,0,.82);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .cc-admin-testimonials .cc-modal-panel {
            background:
                linear-gradient(
                    145deg,
                    rgba(20,17,28,.98),
                    rgba(8,7,12,.99)
                );

            box-shadow:
                0 40px 120px rgba(0,0,0,.65),
                0 0 0 1px rgba(255,255,255,.035);
        }

        /* =========================================================
           SCROLLBAR
        ========================================================= */

        .cc-admin-testimonials .cc-scrollbar::-webkit-scrollbar {
            width: 7px;
        }

        .cc-admin-testimonials .cc-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .cc-admin-testimonials .cc-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.12);
            border-radius: 999px;
        }

        .cc-admin-testimonials .cc-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,.2);
        }

        /* =========================================================
           PAGINATION
        ========================================================= */

        .cc-admin-testimonials nav[aria-label="Pagination Navigation"] {
            margin-top: 1.5rem;
        }

        .cc-admin-testimonials nav[aria-label="Pagination Navigation"] span,
        .cc-admin-testimonials nav[aria-label="Pagination Navigation"] a {
            background: rgba(255,255,255,.025);
            border-color: rgba(255,255,255,.07);
            color: rgba(255,255,255,.45);
        }

        .cc-admin-testimonials nav[aria-label="Pagination Navigation"] a:hover {
            background: rgba(173,211,34,.07);
            color: #ADD322;
        }

        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 640px) {
            .cc-admin-testimonials .mobile-full {
                border-radius: 0;
            }

            .cc-admin-testimonials .testimonial-card.is-highlighted {
                box-shadow:
                    0 0 0 1px rgba(173,211,34,.50),
                    0 0 25px rgba(173,211,34,.28),
                    0 0 55px rgba(173,211,34,.15),
                    0 25px 70px rgba(0,0,0,.45);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .cc-admin-testimonials .testimonial-card,
            .cc-admin-testimonials .testimonial-card.is-highlighted {
                animation: none;
                transition: none;
            }
        }

        .cc-admin-testimonials .testimonial-card {
    position: relative;
}

.cc-admin-testimonials .testimonial-card.is-highlighted {
    border-color: rgba(173, 211, 34, 0.85);

    box-shadow:
        0 0 0 1px rgba(173, 211, 34, 0.35),
        0 0 0 5px rgba(173, 211, 34, 0.04),
        0 0 45px rgba(173, 211, 34, 0.28),
        0 0 100px rgba(139, 92, 246, 0.22),
        0 30px 90px rgba(0, 0, 0, 0.5);

    animation: testimonialHighlightPulse 2.4s ease-in-out infinite;
}

.cc-admin-testimonials .testimonial-card.is-highlighted::after {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    pointer-events: none;
    box-shadow:
        inset 0 0 35px rgba(173, 211, 34, 0.08),
        inset 0 0 80px rgba(139, 92, 246, 0.05);
}

@keyframes testimonialHighlightPulse {
    0%,
    100% {
        box-shadow:
            0 0 0 1px rgba(173, 211, 34, 0.3),
            0 0 35px rgba(173, 211, 34, 0.18),
            0 0 80px rgba(139, 92, 246, 0.12),
            0 30px 90px rgba(0, 0, 0, 0.45);
    }

    50% {
        box-shadow:
            0 0 0 1px rgba(173, 211, 34, 0.65),
            0 0 55px rgba(173, 211, 34, 0.32),
            0 0 110px rgba(139, 92, 246, 0.2),
            0 30px 90px rgba(0, 0, 0, 0.45);
    }
}
    </style>

    {{-- =========================================================
         ATMOSPHERIC BACKGROUND
    ========================================================= --}}

    <div class="pointer-events-none fixed inset-0 -z-10 noise"></div>

    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div
            class="absolute -left-40 -top-40 h-[650px] w-[650px] rounded-full bg-[#8b5cf6]/10 blur-[140px]"
        ></div>

        <div
            class="absolute right-[-220px] top-[5%] h-[600px] w-[600px] rounded-full bg-[#6d28d9]/10 blur-[150px]"
        ></div>

        <div
            class="absolute bottom-[-250px] left-[20%] h-[500px] w-[500px] rounded-full bg-[#ADD322]/[0.025] blur-[150px]"
        ></div>
    </div>

    {{-- =========================================================
         CONTENT
    ========================================================= --}}

    <div class="mx-auto max-w-[1500px] px-4 py-6 sm:px-6 lg:px-10 lg:py-10">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <header class="mb-8 lg:mb-12">
            <div class="flex flex-col gap-6">

                <div>
                    <div class="mb-4 flex items-center gap-3">

                        <span
                            class="status-dot h-2 w-2 rounded-full bg-[#ADD322] text-[#ADD322]"
                        ></span>

                        <span class="text-[11px] font-bold uppercase tracking-[0.3em] text-[#ADD322]">
                            Administration
                        </span>

                    </div>

                    <h1 class="text-4xl font-black tracking-[-0.04em] sm:text-5xl">
                        Testimonials
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-300 sm:text-base">
                        Review customer feedback, moderate submissions and control
                        which testimonials appear publicly on your website.
                    </p>
                </div>

                {{-- =================================================
                     STATS
                ================================================== --}}

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                    {{-- TOTAL --}}
                    <div class="glass rounded-2xl p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                            Total
                        </div>

                        <div class="mt-2 text-2xl font-black">
                            {{ $totalCount }}
                        </div>

                        <div class="mt-1 text-[12px] text-gray-200">
                            All submissions
                        </div>
                    </div>

                    {{-- PENDING --}}
                    <div class="glass rounded-2xl p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                            Pending
                        </div>

                        <div class="mt-2 text-2xl font-black text-yellow-400">
                            {{ $pendingCount }}
                        </div>

                        <div class="mt-1 text-[10px] text-gray-200">
                            Awaiting review
                        </div>
                    </div>

                    {{-- APPROVED --}}
                    <div class="glass rounded-2xl p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                            Approved
                        </div>

                        <div class="mt-2 text-2xl font-black text-[#ADD322]">
                            {{ $approvedCount }}
                        </div>

                        <div class="mt-1 text-[10px] text-gray-200">
                            Published
                        </div>
                    </div>

                    {{-- DENIED --}}
                    <div class="glass rounded-2xl p-4">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                            Denied
                        </div>

                        <div class="mt-2 text-2xl font-black text-red-400">
                            {{ $deniedCount }}
                        </div>

                        <div class="mt-1 text-[10px] text-gray-200">
                            Not published
                        </div>
                    </div>

                </div>

            </div>
        </header>

        {{-- =========================================================
             FLASH MESSAGE
        ========================================================= --}}

        @if (session('testimonial_success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-transition.opacity.duration.250ms
                class="mb-6 overflow-hidden rounded-2xl border border-[#ADD322]/20 bg-[#ADD322]/[0.055] shadow-[0_20px_60px_rgba(0,0,0,.25)]"
            >
                <div class="flex items-center justify-between gap-4 px-4 py-3.5 sm:px-5">

                    <div class="flex min-w-0 items-center gap-3">

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#ADD322]/10 text-[#ADD322]">
                            ✓
                        </span>

                        <p class="text-sm font-medium text-gray-300">
                            {{ session('testimonial_success') }}
                        </p>

                    </div>

                    <button
                        type="button"
                        @click="show = false"
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-300 transition hover:bg-white/5 hover:text-white"
                    >
                        ×
                    </button>

                </div>
            </div>
        @endif

        {{-- =========================================================
             TOOLBAR
        ========================================================= --}}

        <section class="mb-6">

            <div class="glass rounded-2xl p-3 sm:p-4">

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    {{-- FILTERS --}}
                    <div class="flex flex-wrap items-center gap-1.5">

                        @foreach ([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'denied' => 'Denied',
                            'all' => 'All'
                        ] as $value => $label)

                            <button
                                type="button"
                                wire:click="$set('status', '{{ $value }}')"
                                class="filter-button rounded-xl border px-3.5 py-2.5 text-[11px] font-bold transition
                                    {{ $status === $value
                                        ? 'border-[#ADD322]/25 bg-[#ADD322]/[0.08] text-[#ADD322] shadow-[0_8px_25px_rgba(173,211,34,.06)]'
                                        : 'border-transparent text-gray-300 hover:border-white/[0.07] hover:bg-white/[0.025] hover:text-gray-300'
                                    }}"
                            >
                                {{ $label }}

                                @if ($value === 'pending')
                                    <span class="ml-1.5 rounded-full bg-yellow-400/10 px-1.5 py-0.5 text-[9px] text-yellow-400">
                                        {{ $pendingCount }}
                                    </span>
                                @elseif ($value === 'approved')
                                    <span class="ml-1.5 rounded-full bg-[#ADD322]/10 px-1.5 py-0.5 text-[9px] text-[#ADD322]">
                                        {{ $approvedCount }}
                                    </span>
                                @elseif ($value === 'denied')
                                    <span class="ml-1.5 rounded-full bg-red-400/10 px-1.5 py-0.5 text-[9px] text-red-400">
                                        {{ $deniedCount }}
                                    </span>
                                @endif
                            </button>

                        @endforeach

                    </div>

                    {{-- SEARCH --}}
                    <div class="relative w-full lg:max-w-sm">

                        <svg
                            class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-200"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                            />

                            <path
                                stroke-linecap="round"
                                d="m20 20-4-4"
                            />
                        </svg>

                        <input
                            type="search"
                            wire:model.live.debounce.350ms="search"
                            placeholder="Search testimonials..."
                            class="admin-input h-11 w-full rounded-xl pl-10 pr-4 text-xs font-medium text-white"
                        />

                        @if (filled($search))
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute right-2.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-gray-300 transition hover:bg-white/5 hover:text-white"
                            >
                                ×
                            </button>
                        @endif

                    </div>

                </div>

            </div>

        </section>

        {{-- =========================================================
             TESTIMONIALS
        ========================================================= --}}

        <section>

            <div class="mb-5 flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-bold tracking-tight">
                        {{ $status === 'all' ? 'All Testimonials' : ucfirst($status) . ' Testimonials' }}
                    </h2>

                    <p class="mt-1 text-xs text-gray-300">
                        Customer feedback and moderation queue
                    </p>
                </div>

                <span class="rounded-full border border-white/10 bg-white/[0.025] px-3 py-1.5 text-xs font-medium text-gray-300">
                    {{ $testimonials->total() }}
                    {{ \Illuminate\Support\Str::plural('testimonial', $testimonials->total()) }}
                </span>

            </div>

            <div class="grid gap-5 lg:grid-cols-2">

                @forelse ($testimonials as $testimonial)

                    <article
                        id="testimonial-{{ $testimonial->id }}"
                        wire:key="testimonial-{{ $testimonial->id }}"
                        class="testimonial-card {{ $highlightedTestimonialId === $testimonial->id ? 'is-highlighted' : '' }}"
                    >

                        {{-- =================================================
                             TOP
                        ================================================== --}}

                        <div class="relative overflow-hidden">

                            {{-- Glow --}}
                            <div
                                class="pointer-events-none absolute -right-20 -top-24 h-56 w-56 rounded-full bg-[#8b5cf6]/[0.06] blur-[80px] transition duration-500 group-hover:bg-[#8b5cf6]/[0.10]"
                            ></div>

                            <div
                                class="pointer-events-none absolute -bottom-24 -left-20 h-48 w-48 rounded-full bg-[#ADD322]/[0.025] blur-[70px]"
                            ></div>

                            <div class="relative p-5 sm:p-6">

                                {{-- PERSON / STATUS --}}
                                <div class="flex items-start justify-between gap-4">

                                    <div class="flex min-w-0 items-center gap-3.5">

                                        <div class="avatar flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-black text-[#ADD322]">
                                            {{ $testimonial->initials }}
                                        </div>

                                        <div class="min-w-0">

                                            <h3 class="truncate text-base font-bold tracking-tight text-white">
                                                {{ $testimonial->name }}
                                            </h3>

                                            @if ($testimonial->display_role)

                                                <p class="mt-0.5 truncate text-xs text-gray-300">
                                                    {{ $testimonial->display_role }}
                                                </p>

                                            @else

                                                <p class="mt-0.5 text-xs text-gray-200">
                                                    Customer
                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                    @if ($testimonial->status === 'pending')

                                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-yellow-400/15 bg-yellow-400/[0.05] px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.16em] text-yellow-400">

                                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-yellow-400 text-yellow-400"></span>

                                            Pending

                                        </span>

                                    @elseif ($testimonial->status === 'approved')

                                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-[#ADD322]/20 bg-[#ADD322]/[0.055] px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.16em] text-[#ADD322]">

                                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-[#ADD322] text-[#ADD322]"></span>

                                            Approved

                                        </span>

                                    @else

                                        <span class="inline-flex shrink-0 items-center gap-2 rounded-full border border-red-400/15 bg-red-400/[0.045] px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.16em] text-red-400">

                                            <span class="status-dot h-1.5 w-1.5 rounded-full bg-red-400 text-red-400"></span>

                                            Denied

                                        </span>

                                    @endif

                                </div>

                                {{-- META --}}
                                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] text-gray-300">

                                    @if ($testimonial->email)

                                        <span class="inline-flex items-center gap-1.5">

                                            <svg
                                                class="h-3.5 w-3.5 text-gray-200"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                            >
                                                <rect
                                                    x="3"
                                                    y="5"
                                                    width="18"
                                                    height="14"
                                                    rx="2"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m3 7 9 6 9-6"
                                                />
                                            </svg>

                                            {{ $testimonial->email }}

                                        </span>

                                    @endif

                                    <span class="inline-flex items-center gap-1.5">

                                        <svg
                                            class="h-3.5 w-3.5 text-gray-200"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                        >
                                            <rect
                                                x="3"
                                                y="4"
                                                width="18"
                                                height="17"
                                                rx="2"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M16 2v4M8 2v4M3 9h18"
                                            />
                                        </svg>

                                        {{ $testimonial->created_at?->format('M d, Y') }}

                                    </span>

                                </div>

                                {{-- RATING --}}
                                <div class="mt-5 flex items-center gap-3">

                                    <div class="flex items-center gap-0.5">

                                        @for ($star = 1; $star <= 5; $star++)

                                            <span
                                                class="text-sm {{ $testimonial->rating >= $star ? 'text-[#ADD322]' : 'text-gray-800' }}"
                                            >
                                                ★
                                            </span>

                                        @endfor

                                    </div>

                                    <span class="text-[10px] font-bold text-gray-300">
                                        {{ $testimonial->rating }}/5
                                    </span>

                                </div>

                                {{-- MESSAGE --}}
                                <div class="relative mt-5 rounded-2xl border border-white/[0.055] bg-black/[0.16] p-5">

                                    <div class="pointer-events-none absolute right-4 top-1 text-5xl font-serif leading-none text-white/[0.025]">
                                        ”
                                    </div>

                                    <p class="relative text-sm leading-7 text-gray-400">
                                        {{ $testimonial->message }}
                                    </p>

                                </div>

                            </div>

                        </div>

                        {{-- =================================================
                             ACTIONS
                        ================================================== --}}

                        <div class="flex flex-col gap-3 border-t border-white/[0.06] bg-black/[0.08] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                            <div class="text-[9px] font-bold uppercase tracking-[0.2em] text-gray-200">
                                #{{ str_pad($testimonial->id, 4, '0', STR_PAD_LEFT) }}
                            </div>

                            <div class="flex items-center gap-1.5">

                                {{-- APPROVE --}}
                                @if ($testimonial->status !== 'approved')

                                    <button
                                        type="button"
                                        wire:click="confirmApprove({{ $testimonial->id }})"
                                        class="action-button inline-flex items-center gap-2 rounded-xl border border-[#ADD322]/15 bg-[#ADD322]/[0.055] px-3.5 py-2.5 text-[10px] font-bold text-[#ADD322] transition hover:border-[#ADD322]/30 hover:bg-[#ADD322]/[0.10]"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 12 4 4L19 6"
                                            />
                                        </svg>

                                        Approve
                                    </button>

                                @else

                                    {{-- DENY / UNPUBLISH --}}
                                    <button
                                        type="button"
                                        wire:click="confirmDeny({{ $testimonial->id }})"
                                        class="action-button rounded-xl px-3.5 py-2.5 text-[10px] font-bold text-gray-300 transition hover:bg-red-400/[0.06] hover:text-red-400"
                                    >
                                        Unpublish
                                    </button>

                                @endif

                                {{-- DENY --}}
                                @if ($testimonial->status === 'pending')

                                    <button
                                        type="button"
                                        wire:click="confirmDeny({{ $testimonial->id }})"
                                        class="action-button rounded-xl px-3.5 py-2.5 text-[10px] font-bold text-gray-300 transition hover:bg-red-400/[0.06] hover:text-red-400"
                                    >
                                        Deny
                                    </button>

                                @endif

                                {{-- DELETE --}}
                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $testimonial->id }})"
                                    class="action-button rounded-xl px-3.5 py-2.5 text-[10px] font-bold text-gray-200 transition hover:bg-red-500/[0.07] hover:text-red-400"
                                >
                                    Delete
                                </button>

                            </div>

                        </div>

                    </article>

                @empty

                    {{-- =================================================
                         EMPTY
                    ================================================== --}}

                    <div class="glass col-span-full rounded-3xl px-6 py-24 text-center">

                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-[#ADD322]">

                            <svg
                                class="h-7 w-7"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M20 12c0 4.418-3.582 8-8 8a8.96 8.96 0 0 1-4-.93L4 20l.93-4A8.96 8.96 0 0 1 4 12c0-4.418 3.582-8 8-8s8 3.582 8 8Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M8 12h.01M12 12h.01M16 12h.01"
                                />
                            </svg>

                        </div>

                        <h2 class="mt-6 text-2xl font-black">
                            No testimonials here
                        </h2>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-300">

                            @if (filled($search))

                                No testimonials matched your search.

                            @else

                                There are no testimonials in this moderation queue yet.

                            @endif

                        </p>

                        @if (filled($search))

                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="mt-7 rounded-xl border border-white/10 bg-white/[0.025] px-5 py-3 text-xs font-bold text-gray-400 transition hover:border-[#ADD322]/20 hover:bg-[#ADD322]/[0.05] hover:text-[#ADD322]"
                            >
                                Clear search
                            </button>

                        @endif

                    </div>

                @endforelse

            </div>

            {{-- PAGINATION --}}
            @if ($testimonials->hasPages())

                <div class="mt-6">
                    {{ $testimonials->links() }}
                </div>

            @endif

        </section>

    </div>

    {{-- =============================================================
         ACTION MODAL
    ============================================================= --}}

    @if ($showActionModal)

        <div
            x-data="{ open: true }"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.200ms
            class="cc-modal-backdrop fixed inset-0 z-[9999] overflow-y-auto px-3 py-4 sm:px-6 sm:py-8"
            @keydown.escape.window="$wire.cancelAction()"
        >

            <div class="flex min-h-full items-center justify-center">

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-y-4 scale-[.97] opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    class="cc-modal-panel relative w-full max-w-md overflow-hidden rounded-[26px] border border-white/[0.09]"
                    @click.stop
                >

                    <div class="pointer-events-none absolute left-1/2 top-0 h-32 w-2/3 -translate-x-1/2 bg-[#8b5cf6]/[0.08] blur-[70px]"></div>

                    <div class="relative border-b border-white/[0.07] px-5 py-5 sm:px-6">

                        <div class="flex items-start justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border
                                    {{
                                        $pendingAction === 'delete'
                                            ? 'border-red-400/15 bg-red-400/[0.06] text-red-400'
                                            : 'border-[#ADD322]/15 bg-[#ADD322]/[0.06] text-[#ADD322]'
                                    }}"
                                >

                                    @if ($pendingAction === 'delete')

                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                d="M12 9v4"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M12 17h.01"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M10.3 4.7 3.7 16.2A2 2 0 0 0 5.4 19h13.2a2 2 0 0 0 1.7-2.8L13.7 4.7a2 2 0 0 0-3.4 0Z"
                                            />
                                        </svg>

                                    @else

                                        <svg
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 12 4 4L19 6"
                                            />
                                        </svg>

                                    @endif

                                </div>

                                <div>

                                    <div class="text-[9px] font-bold uppercase tracking-[0.24em] text-gray-300">
                                        Confirmation
                                    </div>

                                    <h2 class="mt-0.5 text-lg font-bold tracking-tight text-white">

                                        @switch($pendingAction)

                                            @case('approve')
                                                Approve testimonial
                                                @break

                                            @case('deny')
                                                Deny testimonial
                                                @break

                                            @case('delete')
                                                Delete testimonial
                                                @break

                                        @endswitch

                                    </h2>

                                </div>

                            </div>

                            <button
                                type="button"
                                wire:click="cancelAction"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.08] text-gray-300 transition hover:border-white/15 hover:bg-white/5 hover:text-white"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M6 6l12 12M18 6 6 18"
                                    />
                                </svg>
                            </button>

                        </div>

                    </div>

                    <div class="relative px-5 py-6 sm:px-6">

                        <div
                            class="rounded-2xl border p-4
                            {{
                                $pendingAction === 'delete'
                                    ? 'border-red-400/10 bg-red-400/[0.035]'
                                    : 'border-[#ADD322]/10 bg-[#ADD322]/[0.025]'
                            }}"
                        >

                            <p class="text-sm leading-6 text-gray-400">

                                @switch($pendingAction)

                                    @case('approve')

                                        This testimonial will be approved and published
                                        on your public website.

                                        @break

                                    @case('deny')

                                        This testimonial will be denied and removed
                                        from the public website.

                                        @break

                                    @case('delete')

                                        This testimonial will be permanently removed
                                        from your database. This action cannot be undone.

                                        @break

                                @endswitch

                            </p>

                        </div>

                    </div>

                    <div class="relative flex flex-col-reverse gap-2 border-t border-white/[0.07] bg-black/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">

                        <button
                            type="button"
                            wire:click="cancelAction"
                            class="rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-300 transition hover:bg-white/5 hover:text-white"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            wire:click="executeAction"
                            wire:loading.attr="disabled"
                            wire:target="executeAction"
                            class="inline-flex h-11 items-center justify-center rounded-xl px-5 text-xs font-black transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-50
                            {{
                                $pendingAction === 'delete'
                                    ? 'bg-red-500/90 text-white hover:bg-red-500'
                                    : 'bg-[#ADD322] text-black hover:bg-[#b9df35]'
                            }}"
                        >

                            <span
                                wire:loading.remove
                                wire:target="executeAction"
                            >
                                @switch($pendingAction)

                                    @case('approve')
                                        Approve
                                        @break

                                    @case('deny')
                                        Deny
                                        @break

                                    @case('delete')
                                        Delete permanently
                                        @break

                                @endswitch
                            </span>

                            <span
                                wire:loading
                                wire:target="executeAction"
                            >
                                Processing...
                            </span>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>
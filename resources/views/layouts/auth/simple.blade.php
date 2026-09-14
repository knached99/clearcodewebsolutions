<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

    <style>
        /* ============================================================
           CLEAR CODE — PREMIUM AUTH LAYOUT
        ============================================================ */

        :root {
            --cc-bg: #07050d;
            --cc-purple: #8b5cf6;
            --cc-violet: #7c3aed;
            --cc-fuchsia: #d946ef;
            --cc-border: rgba(255, 255, 255, 0.09);
        }

        html,
        body {
            min-height: 100%;
            background: var(--cc-bg);
        }

        body {
            overflow-x: hidden;
        }

        /* ============================================================
           AMBIENT BACKGROUND
        ============================================================ */

        .cc-auth-page {
            position: relative;
            min-height: 100svh;
            overflow: hidden;
            isolation: isolate;
            background:
                radial-gradient(circle at 15% 15%,
                    rgba(124, 58, 237, 0.16),
                    transparent 30%),
                radial-gradient(circle at 85% 25%,
                    rgba(217, 70, 239, 0.10),
                    transparent 27%),
                radial-gradient(circle at 50% 100%,
                    rgba(79, 70, 229, 0.12),
                    transparent 35%),
                #07050d;
        }

        /*
         * Abstract architectural background.
         * This replaces the generic Unsplash image.
         */
        .cc-auth-page::before {
            content: "";
            position: absolute;
            inset: -30%;
            z-index: -4;
            background:
                linear-gradient(125deg,
                    transparent 0%,
                    rgba(139, 92, 246, 0.035) 30%,
                    transparent 55%),
                repeating-linear-gradient(115deg,
                    rgba(255, 255, 255, 0.018) 0px,
                    rgba(255, 255, 255, 0.018) 1px,
                    transparent 1px,
                    transparent 90px);
            transform: rotate(-5deg);
        }

        /* Fine technical grid */
        .cc-auth-grid {
            position: absolute;
            inset: 0;
            z-index: -3;
            opacity: 0.18;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px,
                    transparent 1px),
                linear-gradient(90deg,
                    rgba(255, 255, 255, 0.035) 1px,
                    transparent 1px);
            background-size: 72px 72px;

            mask-image: linear-gradient(to bottom,
                    black 0%,
                    rgba(0, 0, 0, .75) 55%,
                    transparent 100%);
        }

        /* ============================================================
           LARGE AMBIENT ORBS
        ============================================================ */

        .cc-orb {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
            filter: blur(90px);
            z-index: -2;
        }

        .cc-orb-one {
            width: 32rem;
            height: 32rem;
            left: -14rem;
            top: -10rem;
            background: rgba(124, 58, 237, 0.20);
            animation: ccFloatOne 16s ease-in-out infinite;
        }

        .cc-orb-two {
            width: 30rem;
            height: 30rem;
            right: -13rem;
            top: 12%;
            background: rgba(217, 70, 239, 0.13);
            animation: ccFloatTwo 19s ease-in-out infinite;
        }

        .cc-orb-three {
            width: 25rem;
            height: 25rem;
            left: 35%;
            bottom: -18rem;
            background: rgba(79, 70, 229, 0.15);
            animation: ccFloatThree 22s ease-in-out infinite;
        }

        @keyframes ccFloatOne {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(80px, 50px, 0) scale(1.12);
            }
        }

        @keyframes ccFloatTwo {

            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(-70px, 45px, 0) scale(1.08);
            }
        }

        @keyframes ccFloatThree {

            0%,
            100% {
                transform: translate3d(0, 0, 0);
            }

            50% {
                transform: translate3d(40px, -60px, 0) scale(1.1);
            }
        }

        /* ============================================================
           TOP LIGHT
        ============================================================ */

        .cc-top-light {
            position: absolute;
            top: -18rem;
            left: 50%;
            width: 70rem;
            height: 35rem;
            transform: translateX(-50%);
            border-radius: 50%;
            background:
                radial-gradient(ellipse,
                    rgba(139, 92, 246, 0.13) 0%,
                    rgba(124, 58, 237, 0.04) 38%,
                    transparent 70%);
            filter: blur(20px);
            pointer-events: none;
            z-index: -1;
        }

        /* ============================================================
           MAIN CONTAINER
        ============================================================ */

        .cc-auth-container {
            position: relative;
            z-index: 10;
            width: 100%;
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .cc-auth-layout {
            width: 100%;
            max-width: 1320px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(380px, 500px);
            gap: clamp(3rem, 8vw, 9rem);
            align-items: center;
        }

        /* ============================================================
           BRAND / LEFT CONTENT
        ============================================================ */

        .cc-brand-area {
            position: relative;
            max-width: 640px;
            padding: clamp(1rem, 3vw, 2rem);
        }

        .cc-brand {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: clamp(2.5rem, 6vw, 5rem);
        }

        /* BIGGER LOGO */
        .cc-logo-wrap {
            position: relative;
            width: 72px;
            height: 72px;
            flex-shrink: 0;
        }

        .cc-logo-glow {
            position: absolute;
            inset: -18px;
            border-radius: 28px;
            background:
                radial-gradient(circle,
                    rgba(139, 92, 246, 0.38),
                    transparent 68%);
            filter: blur(18px);
            animation: ccLogoGlow 4s ease-in-out infinite;
        }

        @keyframes ccLogoGlow {

            0%,
            100% {
                opacity: .65;
                transform: scale(.95);
            }

            50% {
                opacity: 1;
                transform: scale(1.08);
            }
        }

        .cc-logo {
            position: relative;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, .14);
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .13),
                    rgba(255, 255, 255, .035));
            box-shadow:
                0 25px 70px rgba(0, 0, 0, .45),
                inset 0 1px 0 rgba(255, 255, 255, .12);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .cc-logo::before {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 21px;
            background:
                linear-gradient(135deg,
                    rgba(139, 92, 246, .25),
                    transparent 45%,
                    rgba(217, 70, 239, .14));
        }

        .cc-logo::after {
            content: "";
            position: absolute;
            width: 120%;
            height: 1px;
            left: -10%;
            bottom: 12px;
            background:
                linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, .3),
                    transparent);
            opacity: .35;
        }

        .cc-logo svg {
            position: relative;
            z-index: 2;
            width: 40px;
            height: 40px;
            filter:
                drop-shadow(0 0 12px rgba(139, 92, 246, .45));
        }

        .cc-brand-copy {
            display: flex;
            flex-direction: column;
        }

        .cc-brand-name {
            font-size: 1.1rem;
            font-weight: 650;
            letter-spacing: -0.025em;
            color: rgba(255, 255, 255, .95);
        }

        .cc-brand-label {
            margin-top: .35rem;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .28em;
            text-transform: uppercase;
            color: rgba(196, 181, 253, .55);
        }

        /* ============================================================
           EYEBROW
        ============================================================ */

        .cc-eyebrow {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .cc-eyebrow-line {
            width: 42px;
            height: 1px;
            background:
                linear-gradient(90deg,
                    rgba(167, 139, 250, .75),
                    transparent);
        }

        .cc-eyebrow-text {
            font-size: .63rem;
            font-weight: 700;
            letter-spacing: .34em;
            text-transform: uppercase;
            color: rgba(196, 181, 253, .62);
        }

        /* ============================================================
           HEADING
        ============================================================ */

        .cc-heading {
            margin: 0;
            font-size: clamp(3.2rem, 6vw, 6.2rem);
            line-height: .94;
            letter-spacing: -.065em;
            font-weight: 650;
            color: #fff;
        }

        .cc-heading-gradient {
            display: block;
            margin-top: .1em;
            background:
                linear-gradient(100deg,
                    #ddd6fe 0%,
                    #a78bfa 30%,
                    #c084fc 62%,
                    #f0abfc 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .cc-description {
            max-width: 570px;
            margin-top: 2rem;
            font-size: clamp(.95rem, 1.4vw, 1.05rem);
            line-height: 1.9;
            color: rgba(255, 255, 255, .42);
        }

        /* ============================================================
           FEATURE PILLS
        ============================================================ */

        .cc-features {
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
            margin-top: 2.5rem;
        }

        .cc-feature {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            min-height: 38px;
            padding: .55rem .8rem;
            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 999px;
            background: rgba(255, 255, 255, .025);
            color: rgba(255, 255, 255, .55);
            font-size: .72rem;
            backdrop-filter: blur(12px);
        }

        .cc-feature-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #a78bfa;
            box-shadow: 0 0 12px rgba(167, 139, 250, .8);
        }

        /* ============================================================
           DECORATIVE CODE PANEL
        ============================================================ */

        .cc-code-panel {
            position: absolute;
            right: -3rem;
            bottom: -5rem;
            width: 270px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, .055);
            border-radius: 18px;
            background: rgba(10, 7, 18, .35);
            box-shadow: 0 30px 90px rgba(0, 0, 0, .35);
            backdrop-filter: blur(20px);
            transform: rotate(-4deg);
            opacity: .65;
            pointer-events: none;
        }

        .cc-code-top {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: .8rem;
        }

        .cc-code-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
        }

        .cc-code-line {
            height: 5px;
            margin-top: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .07);
        }

        .cc-code-line.short {
            width: 45%;
        }

        .cc-code-line.medium {
            width: 68%;
        }

        .cc-code-line.long {
            width: 88%;
        }

        /* ============================================================
           LOGIN SIDE
        ============================================================ */

        .cc-login-area {
            width: 100%;
        }

        .cc-login-shell {
            position: relative;
            width: 100%;
        }

        .cc-login-glow {
            position: absolute;
            inset: -50px;
            border-radius: 60px;
            background:
                radial-gradient(circle at 50% 35%,
                    rgba(139, 92, 246, .14),
                    transparent 65%);
            filter: blur(35px);
            pointer-events: none;
        }

        .cc-login-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .11);
            border-radius: 30px;
            padding: clamp(1.35rem, 4vw, 2.5rem);
            background:
                linear-gradient(145deg,
                    rgba(25, 19, 39, .72),
                    rgba(8, 6, 13, .86));
            box-shadow:
                0 45px 120px rgba(0, 0, 0, .55),
                0 10px 40px rgba(0, 0, 0, .25),
                inset 0 1px 0 rgba(255, 255, 255, .07);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
        }

        .cc-login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg,
                    rgba(139, 92, 246, .09),
                    transparent 28%,
                    transparent 72%,
                    rgba(217, 70, 239, .06));
        }

        .cc-login-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 12%;
            right: 12%;
            height: 1px;
            background:
                linear-gradient(90deg,
                    transparent,
                    rgba(196, 181, 253, .75),
                    transparent);
            box-shadow:
                0 0 25px rgba(139, 92, 246, .5);
        }

        .cc-login-content {
            position: relative;
            z-index: 2;
        }

        /* ============================================================
           MOBILE BRAND
        ============================================================ */

        .cc-mobile-brand {
            display: none;
        }

        /* ============================================================
           FOOTER
        ============================================================ */

        .cc-security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            margin-top: 1.25rem;
            color: rgba(255, 255, 255, .24);
            font-size: .68rem;
        }

        .cc-security-note svg {
            width: 14px;
            height: 14px;
            color: rgba(167, 139, 250, .45);
        }

        /* ============================================================
           RESPONSIVE
        ============================================================ */

        @media (max-width: 1100px) {
            .cc-auth-layout {
                gap: 3rem;
                grid-template-columns: minmax(0, 1fr) minmax(340px, 460px);
            }

            .cc-code-panel {
                display: none;
            }
        }

        @media (max-width: 900px) {
            .cc-auth-container {
                padding: 1rem;
            }

            .cc-auth-layout {
                display: block;
                max-width: 520px;
            }

            .cc-brand-area {
                display: none;
            }

            .cc-mobile-brand {
                display: flex;
                justify-content: center;
                margin-bottom: 1.75rem;
            }

            .cc-mobile-brand-inner {
                display: flex;
                align-items: center;
                gap: .8rem;
            }

            .cc-mobile-logo {
                position: relative;
                width: 52px;
                height: 52px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 17px;
                border: 1px solid rgba(255, 255, 255, .12);
                background: rgba(255, 255, 255, .055);
                box-shadow:
                    0 15px 45px rgba(0, 0, 0, .35),
                    inset 0 1px 0 rgba(255, 255, 255, .1);
                backdrop-filter: blur(20px);
            }

            .cc-mobile-logo svg {
                width: 29px;
                height: 29px;
            }

            .cc-mobile-brand-name {
                font-size: 1rem;
                font-weight: 650;
                color: rgba(255, 255, 255, .95);
            }

            .cc-mobile-brand-label {
                margin-top: .3rem;
                font-size: .55rem;
                font-weight: 700;
                letter-spacing: .24em;
                text-transform: uppercase;
                color: rgba(196, 181, 253, .5);
            }
        }

        @media (max-width: 480px) {
            .cc-auth-container {
                align-items: flex-start;
                padding:
                    max(1rem, env(safe-area-inset-top)) 1rem max(1rem, env(safe-area-inset-bottom));
            }

            .cc-login-card {
                border-radius: 24px;
                padding: 1.2rem;
            }

            .cc-mobile-brand {
                margin-top: 1rem;
                margin-bottom: 1.25rem;
            }

            .cc-security-note {
                margin-bottom: .5rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .cc-orb,
            .cc-logo-glow {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-[#07050d] text-white antialiased">

    <div class="cc-auth-page">

        {{-- ============================================================
             BACKGROUND
        ============================================================= --}}

        <div class="cc-auth-grid" aria-hidden="true"></div>

        <div class="cc-top-light" aria-hidden="true"></div>

        <div class="cc-orb cc-orb-one" aria-hidden="true"></div>
        <div class="cc-orb cc-orb-two" aria-hidden="true"></div>
        <div class="cc-orb cc-orb-three" aria-hidden="true"></div>


        {{-- ============================================================
             MAIN
        ============================================================= --}}

        <main class="cc-auth-container">

            <div class="cc-auth-layout">

                {{-- ====================================================
                     LEFT / BRANDING
                ===================================================== --}}

                <section class="cc-brand-area">

                    {{-- Brand --}}
                    <div class="cc-brand">

                        <div class="cc-logo-wrap">

                            <div class="cc-logo-glow" aria-hidden="true"></div>

                            <div class="cc-logo">
                                <x-app-logo-icon class="fill-current text-white" />
                            </div>

                        </div>

                        <div class="cc-brand-copy">

                            <div class="cc-brand-name">
                                {{ config('app.name', 'ClearCode') }}
                            </div>

                            <div class="cc-brand-label">
                                Developer Support
                            </div>

                        </div>

                    </div>


                    {{-- Eyebrow --}}
                    <div class="cc-eyebrow">

                        <span class="cc-eyebrow-line"></span>

                        <span class="cc-eyebrow-text">
                            Welcome back
                        </span>

                    </div>


                    {{-- Heading --}}
                    <h1 class="cc-heading">

                        Build better.

                        <span class="cc-heading-gradient">
                            Ship faster.
                        </span>

                    </h1>


                    {{-- Description --}}
                    <p class="cc-description">
                        Your development workspace is waiting.
                        Pick up where you left off, manage your work,
                        and keep your projects moving forward.
                    </p>


                    {{-- Feature pills --}}
                    <div class="cc-features">

                        <div class="cc-feature">
                            <span class="cc-feature-dot"></span>
                            Secure workspace
                        </div>

                        <div class="cc-feature">
                            <span class="cc-feature-dot"></span>
                            Projects &amp; requests
                        </div>

                        <div class="cc-feature">
                            <span class="cc-feature-dot"></span>
                            Built for developers
                        </div>

                    </div>


                    {{-- Decorative code panel --}}
                    <div class="cc-code-panel" aria-hidden="true">

                        <div class="cc-code-top">
                            <span class="cc-code-dot"></span>
                            <span class="cc-code-dot"></span>
                            <span class="cc-code-dot"></span>
                        </div>

                        <div class="cc-code-line short"></div>
                        <div class="cc-code-line long"></div>
                        <div class="cc-code-line medium"></div>
                        <div class="cc-code-line long"></div>
                        <div class="cc-code-line short"></div>
                        <div class="cc-code-line medium"></div>

                    </div>

                </section>


                {{-- ====================================================
                     RIGHT / LOGIN
                ===================================================== --}}

                <section class="cc-login-area">

                    {{-- Mobile branding --}}
                    <div class="cc-mobile-brand">

                        <div class="cc-mobile-brand-inner">

                            <div class="cc-mobile-logo">

                                <x-app-logo-icon class="fill-current text-white" />

                            </div>

                            <div>

                                <div class="cc-mobile-brand-name">
                                    {{ config('app.name', 'ClearCode') }}
                                </div>

                                <div class="cc-mobile-brand-label">
                                    Developer Support
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Login --}}
                    <div class="cc-login-shell">

                        <div class="cc-login-glow" aria-hidden="true"></div>

                        <div class="cc-login-card">

                            <div class="cc-login-content">

                                {{ $slot }}

                            </div>

                        </div>

                    </div>


                    {{-- Security note --}}
                    <div class="cc-security-note">

                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.5 2.9 8.5 7 10 4.1-1.5 7-5.5 7-10V6l-7-3Z" stroke="currentColor"
                                stroke-width="1.5" stroke-linejoin="round" />

                            <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        <span>
                            Secure access to your account
                        </span>

                    </div>

                </section>

            </div>

        </main>


        {{-- ============================================================
             TOAST
        ============================================================= --}}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist


        {{-- ============================================================
             FLUX
        ============================================================= --}}

        @fluxScripts

    </div>

</body>

</html>

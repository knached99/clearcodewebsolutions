@php
    $currentTeam = auth()->user()?->currentTeam;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')

    {{-- <style>
        :root {
            --cc-lime: #ADD322;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            background: #050507 !important;
        }

        body {
            min-height: 100vh;
            overflow: hidden;
        }

       
        /* ------------------------------------------------------------
         | Application Shell
         * ------------------------------------------------------------ */

        .cc-app-shell {
            display: flex;
            width: 100%;
            height: 100vh;
            min-height: 100vh;
            overflow: hidden;
            background: #050507;
        }

        .cc-sidebar {
            flex: 0 0 auto;
            height: 100vh !important;
            max-height: 100vh !important;
            background:
                radial-gradient(
                    circle at 15% 0%,
                    rgba(139, 92, 246, 0.10),
                    transparent 28%
                ),
                linear-gradient(
                    180deg,
                    #0a0a0e 0%,
                    #050507 100%
                ) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.07) !important;
            box-shadow: 15px 0 50px rgba(0, 0, 0, 0.28);
        }

        /* ------------------------------------------------------------
         | Sidebar Header
         * ------------------------------------------------------------ */

        .cc-sidebar-header {
            padding: 14px 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .cc-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            min-width: 0;
            text-decoration: none;
        }

        .cc-brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            min-width: 44px;
            overflow: hidden;
            border: 1px solid rgba(173, 211, 34, 0.18);
            border-radius: 12px;
            background: #000;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.025),
                0 8px 24px rgba(0, 0, 0, 0.35);
        }

        .cc-brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            padding: 4px;
            object-fit: contain;
            object-position: center;
        }

        .cc-brand-copy {
            min-width: 0;
            overflow: hidden;
        }

        .cc-brand-name {
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cc-brand-subtitle {
            margin-top: 4px;
            color: var(--cc-lime);
            font-size: 8px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ------------------------------------------------------------
         | Team Switcher
         * ------------------------------------------------------------ */

        .cc-team-switcher {
            margin: 10px 10px 6px;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.055);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.025);
        }

        /* ------------------------------------------------------------
         | Navigation
         * ------------------------------------------------------------ */

        .cc-nav {
            padding: 6px 9px;
        }

        .cc-nav-item {
            position: relative;
            min-height: 40px;
            margin: 2px 0;
            border-radius: 10px;
            color: #92929d !important;
            font-size: 13px;
            font-weight: 600;
            transition:
                color 150ms ease,
                background 150ms ease,
                transform 150ms ease;
        }

        .cc-nav-item:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.045);
            transform: translateX(1px);
        }

        .cc-nav-item[data-current="true"],
        .cc-nav-item[aria-current="page"] {
            color: #fff !important;
            background:
                linear-gradient(
                    90deg,
                    rgba(173, 211, 34, 0.095),
                    rgba(139, 92, 246, 0.045)
                );
        }

        .cc-nav-item[data-current="true"]::before,
        .cc-nav-item[aria-current="page"]::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            bottom: 8px;
            width: 2px;
            border-radius: 999px;
            background: var(--cc-lime);
            box-shadow: 0 0 10px rgba(173, 211, 34, 0.55);
        }

        .cc-nav-item svg {
            width: 17px;
            height: 17px;
        }

        /* ------------------------------------------------------------
         | Sidebar Footer
         * ------------------------------------------------------------ */

        .cc-sidebar-footer {
            padding: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* ------------------------------------------------------------
         | Main Content
         * ------------------------------------------------------------ */

        .cc-main-shell {
            flex: 1 1 auto;
            width: 0;
            min-width: 0;
            height: 100vh;
            min-height: 0;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 80% 0%,
                    rgba(139, 92, 246, 0.035),
                    transparent 30%
                ),
                #050507;
        }

        .cc-main-shell > main,
        .cc-main-shell [data-flux-main] {
            width: 100%;
            height: 100%;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            background: transparent !important;
        }

        /* ------------------------------------------------------------
         | Mobile Header
         * ------------------------------------------------------------ */

        .cc-mobile-header {
            background: #08080c !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
        }

        /* ------------------------------------------------------------
         | Scrollbar
         * ------------------------------------------------------------ */

        .cc-main-shell ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        .cc-main-shell ::-webkit-scrollbar-track {
            background: transparent;
        }

        .cc-main-shell ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.10);
            border-radius: 999px;
        }

        .cc-main-shell ::-webkit-scrollbar-thumb:hover {
            background: rgba(173, 211, 34, 0.35);
        }

        /* ------------------------------------------------------------
         | Mobile
         * ------------------------------------------------------------ */

        @media (max-width: 1023px) {
            body {
                overflow: auto;
            }

            .cc-app-shell {
                height: auto;
                min-height: 100vh;
                overflow: visible;
            }

            .cc-sidebar {
                height: 100dvh !important;
                max-height: 100dvh !important;
            }

            .cc-main-shell {
                width: 100%;
                height: auto;
                min-height: calc(100vh - 57px);
                overflow: visible;
            }

            .cc-main-shell > main,
            .cc-main-shell [data-flux-main] {
                height: auto;
                min-height: calc(100vh - 57px);
                overflow: visible;
            }
        }

        /* ------------------------------------------------------------
         | Order Select
         * ------------------------------------------------------------ */

        .cc-order-select {
            position: relative;
        }

        .cc-order-trigger {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 72px;
            padding: 11px 14px;
            gap: 12px;
            text-align: left;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 16px;
            background:
                linear-gradient(
                    135deg,
                    rgba(139, 92, 246, 0.12),
                    rgba(255, 255, 255, 0.035)
                ),
                rgba(8, 8, 13, 0.92);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.035),
                0 12px 35px rgba(0, 0, 0, 0.2);
            transition:
                border-color 180ms ease,
                box-shadow 180ms ease,
                transform 180ms ease;
        }

        .cc-order-trigger:hover {
            border-color: rgba(173, 211, 34, 0.28);
            box-shadow:
                0 0 0 1px rgba(173, 211, 34, 0.05),
                0 15px 40px rgba(0, 0, 0, 0.28),
                0 0 35px rgba(139, 92, 246, 0.08);
        }

        .cc-order-trigger:focus-visible {
            outline: none;
            border-color: rgba(173, 211, 34, 0.65);
            box-shadow:
                0 0 0 3px rgba(173, 211, 34, 0.08),
                0 0 35px rgba(173, 211, 34, 0.10);
        }

        .cc-order-trigger__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(173, 211, 34, 0.16);
            border-radius: 12px;
            color: #ADD322;
            background:
                linear-gradient(
                    145deg,
                    rgba(173, 211, 34, 0.10),
                    rgba(139, 92, 246, 0.08)
                );
            box-shadow:
                inset 0 0 20px rgba(173, 211, 34, 0.035),
                0 0 25px rgba(173, 211, 34, 0.035);
        }

        .cc-order-trigger__icon svg {
            width: 20px;
            height: 20px;
        }

        .cc-order-trigger__content {
            display: flex;
            flex: 1;
            min-width: 0;
            flex-direction: column;
            gap: 5px;
        }

        .cc-order-trigger__eyebrow {
            color: rgba(255, 255, 255, 0.38);
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .cc-order-trigger__value {
            overflow: hidden;
            color: rgba(255, 255, 255, 0.94);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cc-order-trigger__chevron {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            color: rgba(255, 255, 255, 0.35);
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.035);
            transition:
                color 180ms ease,
                background 180ms ease,
                transform 180ms ease;
        }

        .cc-order-trigger__chevron svg {
            width: 17px;
            height: 17px;
        }

        .cc-order-trigger__chevron.is-open {
            color: #ADD322;
            background: rgba(173, 211, 34, 0.08);
            transform: rotate(180deg);
        }

        .cc-order-dropdown {
            position: absolute;
            z-index: 80;
            top: calc(100% + 9px);
            left: 0;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(139, 92, 246, 0.14),
                    transparent 35%
                ),
                linear-gradient(
                    180deg,
                    rgba(17, 15, 25, 0.98),
                    rgba(7, 7, 11, 0.99)
                );
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.55),
                0 0 0 1px rgba(139, 92, 246, 0.04),
                0 0 60px rgba(139, 92, 246, 0.08);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .cc-order-search {
            position: relative;
            display: flex;
            align-items: center;
            margin: 10px;
            height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.035);
        }

        .cc-order-search > svg {
            flex: 0 0 auto;
            width: 17px;
            height: 17px;
            margin-left: 13px;
            color: rgba(255, 255, 255, 0.32);
        }

        .cc-order-search__input {
            width: 100%;
            height: 100%;
            padding: 0 38px 0 10px;
            color: #fff;
            font-size: 12px;
            border: 0;
            outline: 0;
            background: transparent;
        }

        .cc-order-search__input::placeholder {
            color: rgba(255, 255, 255, 0.28);
        }

        .cc-order-search__clear {
            position: absolute;
            right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            color: rgba(255, 255, 255, 0.38);
            font-size: 18px;
            border-radius: 7px;
        }

        .cc-order-search__clear:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.06);
        }

        .cc-order-options {
            max-height: 310px;
            padding: 2px 8px 9px;
            overflow-y: auto;
        }

        .cc-order-options::-webkit-scrollbar {
            width: 5px;
        }

        .cc-order-options::-webkit-scrollbar-track {
            background: transparent;
        }

        .cc-order-options::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
        }

        .cc-order-option {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 61px;
            padding: 9px;
            gap: 11px;
            text-align: left;
            border: 1px solid transparent;
            border-radius: 12px;
            background: transparent;
            transition:
                background 150ms ease,
                border-color 150ms ease,
                transform 150ms ease;
        }

        .cc-order-option:hover {
            border-color: rgba(255, 255, 255, 0.06);
            background: rgba(255, 255, 255, 0.045);
            transform: translateX(2px);
        }

        .cc-order-option.is-selected {
            border-color: rgba(173, 211, 34, 0.16);
            background:
                linear-gradient(
                    90deg,
                    rgba(173, 211, 34, 0.09),
                    rgba(139, 92, 246, 0.05)
                );
        }

        .cc-order-option__number {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            color: rgba(255, 255, 255, 0.38);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.04em;
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.025);
        }

        .cc-order-option__number svg {
            width: 16px;
            height: 16px;
            color: #ADD322;
        }

        .cc-order-option.is-selected .cc-order-option__number {
            color: #ADD322;
            border-color: rgba(173, 211, 34, 0.20);
            background: rgba(173, 211, 34, 0.07);
        }

        .cc-order-option__copy {
            display: flex;
            flex: 1;
            min-width: 0;
            flex-direction: column;
            gap: 4px;
        }

        .cc-order-option__title {
            overflow: hidden;
            color: rgba(255, 255, 255, 0.88);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cc-order-option__subtitle {
            overflow: hidden;
            color: rgba(255, 255, 255, 0.30);
            font-size: 9px;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cc-order-check {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 25px;
            width: 25px;
            height: 25px;
            color: #ADD322;
            border: 1px solid rgba(173, 211, 34, 0.20);
            border-radius: 50%;
            background: rgba(173, 211, 34, 0.08);
            box-shadow: 0 0 18px rgba(173, 211, 34, 0.08);
        }

        .cc-order-check svg {
            width: 14px;
            height: 14px;
        }

        .cc-order-divider {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 10px 7px 7px;
        }

        .cc-order-divider::before,
        .cc-order-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.055);
        }

        .cc-order-divider span {
            color: rgba(255, 255, 255, 0.22);
            font-size: 7px;
            font-weight: 900;
            letter-spacing: 0.16em;
            white-space: nowrap;
        }

        .cc-order-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 9px;
            padding: 30px 15px;
            color: rgba(255, 255, 255, 0.30);
            font-size: 11px;
        }

        .cc-order-empty__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            color: rgba(255, 255, 255, 0.24);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.025);
        }

        .cc-order-empty__icon svg {
            width: 17px;
            height: 17px;
        }

        .cc-form-help {
            margin-top: 8px;
            color: rgba(255, 255, 255, 0.34);
            font-size: 10px;
            line-height: 1.6;
        }

        [x-cloak] {
            display: none !important;
        }

        @media (max-width: 640px) {
            .cc-order-dropdown {
                position: fixed;
                top: 50%;
                left: 50%;
                width: min(92vw, 480px);
                max-height: 70vh;
                transform: translate(-50%, -50%);
            }

            .cc-order-options {
                max-height: calc(70vh - 70px);
            }
        }
    </style> --}}
</head>

<body class="min-h-screen bg-[#050507] text-white">

    {{-- Application Loader --}}
    <div
        id="cc-loader"
        class="cc-loader"
        role="status"
        aria-label="Loading"
    >
        <div class="cc-loader__content">
            <div class="cc-loader__glow"></div>

            <div class="cc-loader__halo"></div>

            <img
                src="{{ asset('assets/img/loader.svg') }}"
                alt=""
                class="cc-loader__image"
            >
        </div>
    </div>

    <div class="cc-app-shell">

        <flux:sidebar
            sticky
            collapsible="mobile"
            class="cc-sidebar"
        >

            <flux:sidebar.header class="cc-sidebar-header">

                <a
                    href="{{ $currentTeam
                        ? route('dashboard', ['current_team' => $currentTeam->slug])
                        : route('onboarding') }}"
                    wire:navigate
                    class="cc-brand"
                >
                    <div class="cc-brand-logo">
                        <img
                            src="{{ asset('assets/img/app__logo.jpg') }}"
                            alt="ClearCode WebSolutions"
                        >
                    </div>

                    <div class="cc-brand-copy">
                        <div class="cc-brand-name">
                            ClearCode
                        </div>

                        <div class="cc-brand-subtitle">
                            WebSolutions
                        </div>
                    </div>
                </a>

                <flux:sidebar.collapse class="lg:hidden" />

            </flux:sidebar.header>

            <div class="cc-team-switcher">
                <livewire:team-switcher />
            </div>

            <flux:sidebar.nav class="cc-nav">

                <flux:sidebar.group
                    :heading="__('Platform')"
                    class="grid"
                >

                    @if ($currentTeam)
                        <flux:sidebar.item
                            icon="home"
                            :href="route('dashboard', [
                                'current_team' => $currentTeam->slug,
                            ])"
                            :current="request()->routeIs('dashboard')"
                            wire:navigate
                            class="cc-nav-item"
                        >
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                    @endif

                </flux:sidebar.group>

                <flux:sidebar.group
                    :heading="__('Work')"
                    class="grid"
                >

                    @if ($currentTeam)

                        <flux:sidebar.item
                            icon="folder"
                            :href="route('teams.projects.index', [
                                'current_team' => $currentTeam->slug,
                            ])"
                            :current="request()->routeIs('teams.projects.index')"
                            wire:navigate
                            class="cc-nav-item"
                        >
                            {{ __('Projects') }}
                        </flux:sidebar.item>

                        {{-- FIXED: testimonials route requires current_team --}}
                        @if ($currentTeam)
                        <flux:sidebar.item
                            icon="chat-bubble-left-right"
                            :href="route('testimonials.index', [
                                'current_team' => $currentTeam->slug,
                            ])"
                            :current="request()->routeIs('testimonials.index', 'testimonials.show')"
                            wire:navigate
                            class="cc-nav-item"
                        >
                            {{ __('Testimonials') }}
                        </flux:sidebar.item>
                    @endif

                    @endif

                </flux:sidebar.group>

                <flux:sidebar.group
                    :heading="__('System')"
                    class="grid"
                >

                    <flux:sidebar.item
                        icon="cog-6-tooth"
                        :href="route('profile.edit')"
                        :current="request()->routeIs('profile.edit')"
                        wire:navigate
                        class="cc-nav-item"
                    >
                        {{ __('Settings') }}
                    </flux:sidebar.item>

                </flux:sidebar.group>

            </flux:sidebar.nav>

            <flux:spacer />

            <div class="cc-sidebar-footer">

                <x-desktop-user-menu
                    class="hidden lg:block"
                    :name="auth()->user()->name"
                />

            </div>

        </flux:sidebar>

        <div class="cc-main-shell">
            {{ $slot }}
        </div>

    </div>

    <flux:header class="cc-mobile-header lg:hidden">

        <flux:sidebar.toggle
            class="lg:hidden"
            icon="bars-2"
            inset="left"
        />

        <flux:spacer />

        <flux:dropdown
            position="top"
            align="end"
        >

            <flux:profile
                :initials="auth()->user()->initials()"
                icon-trailing="chevron-down"
            />

            <flux:menu>

                <flux:menu.radio.group>

                    <div class="p-0 text-sm font-normal">

                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">

                            <flux:avatar
                                :name="auth()->user()->name"
                                :initials="auth()->user()->initials()"
                            />

                            <div class="grid flex-1 text-start text-sm leading-tight">

                                <flux:heading class="truncate">
                                    {{ auth()->user()->name }}
                                </flux:heading>

                                <flux:text class="truncate">
                                    {{ auth()->user()->email }}
                                </flux:text>

                            </div>

                        </div>

                    </div>

                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>

                    <flux:menu.item
                        :href="route('profile.edit')"
                        icon="cog"
                        wire:navigate
                    >
                        {{ __('Settings') }}
                    </flux:menu.item>

                </flux:menu.radio.group>

                <flux:menu.separator />

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="w-full"
                >
                    @csrf

                    <flux:menu.item
                        as="button"
                        type="submit"
                        icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer"
                        data-test="logout-button"
                    >
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>

            </flux:menu>

        </flux:dropdown>

    </flux:header>

    <livewire:required-onboarding />
    <livewire:create-team-modal />

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    <script>
        (() => {
            const hideLoader = () => {
                const loader = document.getElementById('cc-loader');

                if (!loader) {
                    return;
                }

                loader.classList.add('is-hidden');

                setTimeout(() => {
                    loader.remove();
                }, 500);
            };

            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                window.addEventListener('load', hideLoader, {
                    once: true
                });
            }
        })();
    </script>

    @fluxScripts

</body>
</html>

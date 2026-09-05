<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

    <style>
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

        /*
        |--------------------------------------------------------------------------
        | Application shell
        |--------------------------------------------------------------------------
        */

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

            box-shadow:
                15px 0 50px rgba(0, 0, 0, 0.28);
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar header
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Team switcher
        |--------------------------------------------------------------------------
        */

        .cc-team-switcher {
            margin: 10px 10px 6px;
            padding: 2px;

            border: 1px solid rgba(255, 255, 255, 0.055);
            border-radius: 12px;

            background: rgba(255, 255, 255, 0.025);
        }

        /*
        |--------------------------------------------------------------------------
        | Navigation
        |--------------------------------------------------------------------------
        */

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

            box-shadow:
                0 0 10px rgba(173, 211, 34, 0.55);
        }

        .cc-nav-item svg {
            width: 17px;
            height: 17px;
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar footer
        |--------------------------------------------------------------------------
        */

        .cc-sidebar-footer {
            padding: 10px;

            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        /*
        |--------------------------------------------------------------------------
        | Main content
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Flux main
        |--------------------------------------------------------------------------
        */

        .cc-main-shell > main,
        .cc-main-shell [data-flux-main] {
            width: 100%;
            height: 100%;
            min-height: 0;

            overflow-y: auto;
            overflow-x: hidden;

            background: transparent !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile header
        |--------------------------------------------------------------------------
        */

        .cc-mobile-header {
            background: #08080c !important;

            border-bottom: 1px solid rgba(255, 255, 255, 0.07) !important;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, 0.25);
        }

        /*
        |--------------------------------------------------------------------------
        | Scrollbar
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

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
    </style>
</head>

<body class="min-h-screen bg-[#050507] text-white">

    <div class="cc-app-shell">

        <flux:sidebar
            sticky
            collapsible="mobile"
            class="cc-sidebar"
        >

            <flux:sidebar.header class="cc-sidebar-header">

                <a
                    href="{{ route('dashboard') }}"
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

                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                        class="cc-nav-item"
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                </flux:sidebar.group>

                <flux:sidebar.group
                    :heading="__('Work')"
                    class="grid"
                >

                    <flux:sidebar.item
                        icon="folder"
                        :href="route('teams.projects.index')"
                        :current="request()->routeIs('teams.projects.index')"
                        wire:navigate
                        class="cc-nav-item"
                    >
                        {{ __('Projects') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="chat-bubble-left-right"
                        :href="route('testimonials.index')"
                        :current="request()->routeIs('testimonials.index', 'testimonials.show')"
                        wire:navigate
                        class="cc-nav-item"
                    >
                        {{ __('Testimonials') }}
                    </flux:sidebar.item>

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

    <livewire:create-team-modal />

    @persist('toast')

        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>

    @endpersist

    @fluxScripts

</body>

</html>
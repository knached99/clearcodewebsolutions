<nav class="cc-navbar" id="cc-navbar">

    {{-- Ambient glow --}}
    <div class="cc-navbar__glow"></div>

    <div class="cc-navbar__container">

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="cc-navbar__brand">
            <img
                src="{{ asset('assets/img/app__logo.jpg') }}"
                alt="ClearCode Web Solutions"
                class="cc-navbar__logo"
            >

            <span class="cc-navbar__logo-name">
                ClearCode
                <small>Web Solutions</small>
            </span>
        </a>


        {{-- Desktop Navigation --}}
        <div class="cc-navbar__desktop">

            <a href="{{ url('/') }}" class="cc-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <span>Home</span>
            </a>

            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#services' : '#services' }}"
                class="cc-nav-link"
            >
                <span>Services</span>
            </a>

        
            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#testimonials' : '#testimonials' }}"
                class="cc-nav-link"
            >
                <span>Testimonials</span>
            </a>
    

            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#pricing' : '#pricing' }}"
                class="cc-nav-link"
            >
                <span>Pricing</span>
            </a>



            <a
                href="{{ route('testimonials.submit') }}" class="cc-nav-link {{ request()->routeIs('testimonials.submit') ? 'active' : '' }}">
                <span>Submit Testimonial</span>
            </a>
                {{-- href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#testimonial-form' : '#testimonial-form' }}" --}}
           

            <a
                href="{{ route('home.tickets.ticket-search') }}"
                class="cc-nav-link {{ request()->routeIs('home.tickets.ticket-search', 'tickets.show') ? 'active' : '' }}"
            >
                <span>Track Ticket</span>
            </a>

        </div>


        {{-- Desktop CTA --}}
        <a
            href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#contact' : '#contact' }}"
            class="cc-navbar__cta"
        >
            <span>Let's Talk</span>

            <svg viewBox="0 0 24 24">
                <path
                    d="M5 12h14M13 6l6 6-6 6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </a>


        {{-- Mobile Menu Button --}}
        <button
            type="button"
            class="cc-menu-button"
            id="cc-navbar-toggle"
            aria-label="Open navigation"
            aria-expanded="false"
        >
            <span class="cc-menu-button__label">
                Menu
            </span>

            <span class="cc-menu-button__icon">
                <i></i>
                <i></i>
            </span>
        </button>

    </div>


    {{-- Mobile Overlay --}}
    <div
        class="cc-mobile-overlay"
        id="cc-mobile-overlay"
    ></div>


    {{-- Mobile Drawer --}}
    <aside
        class="cc-mobile-drawer"
        id="cc-mobile-menu"
        aria-hidden="true"
    >

        <div class="cc-mobile-drawer__top">

            <a
                href="{{ url('/') }}"
                class="cc-mobile-drawer__logo"
            >
                <span class="cc-navbar__logo-icon">
                    C
                </span>

                <span class="cc-navbar__logo-name">
                    ClearCode
                    <small>Web Solutions</small>
                </span>
            </a>

            <button
                type="button"
                class="cc-mobile-close"
                id="cc-mobile-close"
                aria-label="Close navigation"
            >
                <span></span>
                <span></span>
            </button>

        </div>


        <div class="cc-mobile-drawer__line"></div>

        <div class="cc-mobile-drawer__scroll">    
        <div class="cc-mobile-navigation">

            <p class="cc-mobile-navigation__label">
                Navigation
            </p>


            {{-- Home --}}
            <a
                href="{{ url('/') }}"
                class="cc-mobile-link active"
            >
                <span class="cc-mobile-link__number">
                    01
                </span>

                <span class="cc-mobile-link__text">
                    Home
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>


            {{-- Services --}}
            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#services' : '#services' }}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    02
                </span>

                <span class="cc-mobile-link__text">
                    Services
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>

            
            {{-- Testimonials --}}
            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#testimonials' : '#testimonials' }}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    04
                </span>

                <span class="cc-mobile-link__text">
                    Testimonials
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>




             {{-- Pricing --}}
            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#pricing' : '#pricing' }}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    03
                </span>

                <span class="cc-mobile-link__text">
                    Pricing
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>


            {{-- Submit Testimonial --}}
            <a
                href="{{request()->routeIs('testimonials.submit') ? request()->fullUrl() : route('testimonials.submit')}}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    05
                </span>

                <span class="cc-mobile-link__text">
                    Submit Testimonial
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>


            {{-- Contact --}}
            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#contact' : '#contact' }}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    06
                </span>

                <span class="cc-mobile-link__text">
                    Contact
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>


            {{-- Track Ticket --}}
            <a
                href="{{ route('home.tickets.ticket-search') }}"
                class="cc-mobile-link"
            >
                <span class="cc-mobile-link__number">
                    07
                </span>

                <span class="cc-mobile-link__text">
                    Track Ticket
                </span>

                <span class="cc-mobile-link__arrow">
                    ↗
                </span>
            </a>

        </div>
        </div>


        <div class="cc-mobile-drawer__bottom">

            <a
                href="{{ request()->routeIs('home.tickets.ticket-search') || request()->routeIs('work.show') || request()->routeIs('tickets.show') ? url('/').'#contact' : '#contact' }}"
                class="cc-mobile-cta"
            >
                <span>
                    Let's build something
                    <strong>great.</strong>
                </span>

                <span class="cc-mobile-cta__arrow">
                    →
                </span>
            </a>


            <div class="cc-mobile-drawer__footer">

                <span>
                    © {{ date('Y') }} ClearCode
                </span>

                <span>
                    Houston, TX
                </span>

            </div>

        </div>

    </aside>

</nav>
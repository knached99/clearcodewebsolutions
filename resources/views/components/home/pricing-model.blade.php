{{-- =============================================================
     PRICING
     ============================================================= --}}

<section
    id="pricing"
    class="pricing-section"
    x-data="{ billing: 'one-time' }"
>
    <div class="container-fluid px-4">
        <div class="row gx-5 justify-content-center">
            <div class="col-12 col-xl-11">

                {{-- =================================================
                     HEADER
                     ================================================= --}}
                <div class="pricing-header">

                    <div class="pricing-eyebrow">
                        PRICING
                    </div>

                    <h2>
                        Simple pricing.<br>
                        No surprises.
                    </h2>

                    <p>
                        Choose the payment model that works best
                        for your project.
                    </p>

                </div>


                {{-- =================================================
                     BILLING SWITCH
                     ================================================= --}}
                <div class="pricing-switch-wrapper">

                    <div
                        class="pricing-switch"
                        :class="{ 'monthly-active': billing === 'monthly' }"
                    >

                        <div class="pricing-switch__pill"></div>

                        <button
                            type="button"
                            class="pricing-switch__button"
                            :class="{ 'is-active': billing === 'one-time' }"
                            @click="billing = 'one-time'"
                        >
                            One Time
                        </button>

                        <button
                            type="button"
                            class="pricing-switch__button"
                            :class="{ 'is-active': billing === 'monthly' }"
                            @click="billing = 'monthly'"
                        >
                            Monthly
                        </button>

                    </div>

                </div>


                {{-- =================================================
                     PRICING VIEWPORT
                     ================================================= --}}
                <div class="pricing-slider">

                    {{-- =================================================
                         PRICING TRACK
                         ================================================= --}}
                    <div
                        class="pricing-slider__track"
                        :class="{ 'is-monthly': billing === 'monthly' }"
                    >


                        {{-- =================================================
                             ONE-TIME
                             ================================================= --}}
                        <div class="pricing-slider__group">

                            <div class="pricing-grid">


                                {{-- =================================================
                                     GROWTH WEBSITE
                                     ================================================= --}}
                                <div class="pricing-card">

                                    <div class="pricing-card__top">

                                        <span class="pricing-card__label">
                                            ONE-TIME FEE
                                        </span>

                                        <h3 class="pricing-card__title">
                                            Basic                                            
                                        </h3>

                                        <p>
                                            A professional website designed
                                            to establish a strong online
                                            presence for your business.
                                        </p>

                                        <div class="pricing-card__meta">
                                            Delivery: 2–3 weeks
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">

                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            800
                                        </span>

                                        <span class="pricing-card__suffix">
                                            Basic
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Up to 5 pages
                                            <small>
                                                Home, About, Services, Contact plus 1 additional page
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Custom designed,
                                            mobile responsive layout
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Clean, scalable code built
                                            for long term performance
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Basic on page SEO setup
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Custom contact form
                                        </li>

                                        <li>
                                            <span>+</span>
                                            1–2 rounds of revisions
                                        </li>

                                    </ul>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Start a project
                                    </a>

                                </div>


                                {{-- =================================================
                                     BUSINESS WEBSITE
                                     ================================================= --}}
                                <div class="pricing-card pricing-card--featured">
                                  <span class="pricing-card__label">
                                            ONE TIME FEE
                                        </span>

                                    <div class="pricing-card__top">


                                        <h3 class="pricing-card__title">
                                        <span>Growth</span>
                                        <span class="pricing-card__badge">MOST POPULAR</span>
                                    </h3>
                                                                        

                                        <p>
                                            A powerful website built around
                                            your brand, content, and long-term
                                            business growth.
                                        </p>
                                        

                                        <div class="pricing-card__meta">
                                            Delivery: 4–6 weeks
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">

                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            1,500
                                        </span>

                                        <span class="pricing-card__suffix">
                                            starting
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Up to 10 pages
                                            <small>
                                                Custom designed and built to your brand
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Mobile responsive design
                                            across all devices
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Simple content management
                                            <small>
                                                Update your own text and images with ease
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            SEO optimization +
                                            Google Analytics setup
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Dynamic, database driven pages
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Basic copywriting assistance
                                        </li>

                                        <li>
                                            <span>+</span>
                                            3 rounds of revisions
                                        </li>

                                    </ul>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Start a project
                                    </a>

                                </div>


                                {{-- =================================================
                                     CUSTOM / PREMIUM
                                     ================================================= --}}
                                <div class="pricing-card">

                                    <div class="pricing-card__top">

                                        <span class="pricing-card__label">
                                            PROJECT BASED
                                        </span>

                                        <h3 class="pricing-card__title">
                                            Custom / Premium Website
                                        </h3>

                                        <p>
                                            A fully custom digital experience
                                            architected around your business needs.
                                        </p>

                                        <div class="pricing-card__meta">
                                            Delivery: 6–10+ weeks
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">


                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            3,500
                                        </span>

                                        <span class="pricing-card__suffix">
                                            starting
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Fully custom website
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Mobile responsive design
                                            across all devices
                                        </li>

                                        <li>
                                            <span>+</span>
                                            App development included
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Custom functionality
                                            <small>
                                                User accounts, bookings,
                                                e-commerce, and more
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Secure, scalable infrastructure
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Advanced SEO strategy
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Content strategy support
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Ongoing revisions until final sign-off
                                        </li>

                                    </ul>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Discuss your project
                                    </a>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             MONTHLY
                             ================================================= --}}
                              {{-- Monthly intro --}}
                            {{-- <div class="pricing-monthly-intro">

                                <div class="pricing-monthly-intro__label">
                                    MONTHLY SUBSCRIPTION MODEL
                                </div>

                                <p>
                                    Get a professional website today with no
                                    large upfront cost — we build it, host it,
                                    maintain it, and keep improving it, all for
                                    one predictable monthly fee.
                                </p>

                            </div> --}}
                        <div class="pricing-slider__group">

                            <div class="pricing-grid">


                                {{-- =================================================
                                     STARTER
                                     ================================================= --}}
                                <div class="pricing-card">

                                    <div class="pricing-card__top">

                                        <span class="pricing-card__label">
                                            MONTHLY
                                        </span>

                                        <h3 class="pricing-card__title">
                                            Starter Partnership
                                        </h3>

                                        <p>
                                            Everything you need to launch
                                            and maintain a professional
                                            online presence.
                                        </p>

                                        <div class="pricing-card__meta">
                                            12-month minimum term
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">

                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            99
                                        </span>

                                        <span class="pricing-card__suffix">
                                            / month
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Up to 5-page mobile-responsive website
                                            <small>
                                                Built & owned by you, hosted by us
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Hosting, domain management & SSL
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Monthly backups & security monitoring
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Software/plugin updates
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Minor content edits
                                            <small>
                                                30 minutes per month
                                            </small>
                                        </li>

                                    </ul>


                                    <div class="pricing-card__note">
                                        12-month total:
                                        <strong>$1,188</strong>
                                    </div>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Get started
                                    </a>

                                </div>


                                {{-- =================================================
                                     GROWTH
                                     ================================================= --}}
                                <div class="pricing-card pricing-card--featured">

                                    <div class="pricing-card__top">

                                        <span class="pricing-card__label">
                                            MONTHLY
                                        </span>

                                        <h3 class="pricing-card__title">
                                        <span>Growth Partnership</span>
                                        <span class="pricing-card__badge">MOST POPULAR</span>
                                    </h3>

                                        <p>
                                            A complete website partnership
                                            with ongoing optimization,
                                            support, and improvements.
                                        </p>

                                        <div class="pricing-card__meta">
                                            12-month minimum term
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">

                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            199
                                        </span>

                                        <span class="pricing-card__suffix">
                                            / month
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Up to 10-page semi-custom website
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Everything in Starter
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Blog / CMS setup
                                        </li>

                                        <li>
                                            <span>+</span>
                                            SEO monitoring +
                                            monthly performance report
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Up to 2 hours of content updates/mo
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Priority support
                                            <small>
                                                24–48 hour response
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Quarterly design refresh
                                        </li>

                                    </ul>


                                    <div class="pricing-card__note">
                                        12-month total:
                                        <strong>$2,388</strong>
                                    </div>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Get started
                                    </a>

                                </div>


                                {{-- =================================================
                                     PREMIUM
                                     ================================================= --}}
                                <div class="pricing-card">

                                    <div class="pricing-card__top">

                                        <span class="pricing-card__label">
                                            MONTHLY
                                        </span>

                                        <h3 class="pricing-card__title">
                                            Premium Partnership
                                        </h3>

                                        <p>
                                            A fully custom digital partnership
                                            for businesses requiring advanced
                                            functionality.
                                        </p>

                                        <div class="pricing-card__meta">
                                            12-month minimum term
                                        </div>

                                    </div>


                                    <div class="pricing-card__price">

                                        <span class="pricing-card__currency">
                                            $
                                        </span>

                                        <span class="pricing-card__amount">
                                            399
                                        </span>

                                        <span class="pricing-card__suffix">
                                            / month
                                        </span>

                                    </div>


                                    <div class="pricing-card__divider"></div>


                                    <ul class="pricing-card__features">

                                        <li>
                                            <span>+</span>
                                            Fully custom website
                                        </li>

                                        <li>
                                            <span>+</span>
                                            App development included
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Everything in Growth
                                        </li>

                                        <li>
                                            <span>+</span>
                                            E-commerce / booking integration
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Dedicated support line
                                            <small>
                                                Same-day response
                                            </small>
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Up to 5 hours of updates/design work
                                            per month
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Quarterly strategy call
                                        </li>

                                        <li>
                                            <span>+</span>
                                            Free minor feature additions
                                        </li>

                                    </ul>


                                    <div class="pricing-card__note">
                                        12-month total:
                                        <strong>$4,788</strong>
                                    </div>


                                    <a
                                        href="#contact"
                                        class="pricing-card__button"
                                    >
                                        Get started
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

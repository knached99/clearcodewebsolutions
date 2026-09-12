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

            <div class="col-12 col-xl-10">

                {{-- Header --}}
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

                        {{-- Animated background --}}
                        <div class="pricing-switch__pill"></div>


                        {{-- One Time --}}
                        <button
                            type="button"
                            class="pricing-switch__button"
                            :class="{ 'is-active': billing === 'one-time' }"
                            @click="billing = 'one-time'"
                        >
                            One Time
                        </button>


                        {{-- Monthly --}}
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
                     PRICING CARDS
                     ================================================= --}}
                <div class="pricing-grid">


                    {{-- =================================================
                         ONE TIME
                         ================================================= --}}
                    <div
                        class="pricing-card"
                        x-show="billing === 'one-time'"
                        x-transition:enter="pricing-enter"
                        x-transition:enter-start="pricing-enter-start"
                        x-transition:enter-end="pricing-enter-end"
                        x-transition:leave="pricing-leave"
                        x-transition:leave-start="pricing-leave-start"
                        x-transition:leave-end="pricing-leave-end"
                    >

                        <div class="pricing-card__top">

                            <span class="pricing-card__label">
                                ONE TIME
                            </span>

                            <h3>
                                Project Based
                            </h3>

                            <p>
                                A complete website built around
                                your business and goals.
                            </p>

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
                                Custom website design
                            </li>

                            <li>
                                <span>+</span>
                                Responsive development
                            </li>

                            <li>
                                <span>+</span>
                                SEO-ready structure
                            </li>

                            <li>
                                <span>+</span>
                                Contact forms & integrations
                            </li>

                            <li>
                                <span>+</span>
                                Performance optimization
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
                         MONTHLY
                         ================================================= --}}
                    <div
                        class="pricing-card pricing-card--featured"
                        x-show="billing === 'monthly'"
                        x-transition:enter="pricing-enter"
                        x-transition:enter-start="pricing-enter-start"
                        x-transition:enter-end="pricing-enter-end"
                        x-transition:leave="pricing-leave"
                        x-transition:leave-start="pricing-leave-start"
                        x-transition:leave-end="pricing-leave-end"
                    >

                        <div class="pricing-card__top">

                            <span class="pricing-card__label">
                                MONTHLY
                            </span>

                            <h3>
                                Website Care
                            </h3>

                            <p>
                                Keep your website fast, secure,
                                updated, and continuously improving.
                            </p>

                        </div>


                        <div class="pricing-card__price">

                            <span class="pricing-card__currency">
                                $
                            </span>

                            <span class="pricing-card__amount">
                                199
                            </span>

                            <span class="pricing-card__suffix">
                                / month (starting)
                            </span>

                        </div>


                        <div class="pricing-card__divider"></div>


                        <ul class="pricing-card__features">

                            <li>
                                <span>+</span>
                                Website hosting
                            </li>

                            <li>
                                <span>+</span>
                                Security & maintenance
                            </li>

                            <li>
                                <span>+</span>
                                Content updates
                            </li>

                            <li>
                                <span>+</span>
                                Performance monitoring
                            </li>

                            <li>
                                <span>+</span>
                                Ongoing technical support
                            </li>

                        </ul>


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
</section>

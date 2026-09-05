<section
    id="testimonials"
    class="cc-testimonials"
    x-data="{
        current: 0,
        total: {{ $testimonials->count() }},
        autoplay: null,

        start() {
            if (this.total <= 1) return;
            this.stop();

            this.autoplay = setInterval(() => {
                this.next();
            }, 6000);
        },

        stop() {
            if (this.autoplay) {
                clearInterval(this.autoplay);
                this.autoplay = null;
            }
        },

        next() {
            if (this.total <= 1) return;
            this.current = (this.current + 1) % this.total;
        },

        prev() {
            if (this.total <= 1) return;
            this.current = (this.current - 1 + this.total) % this.total;
        },

        goTo(index) {
            if (index === this.current) return;
            this.current = index;
        }
    }"
    x-init="start()"
    x-on:mouseenter="stop()"
    x-on:mouseleave="start()"
    x-on:keydown.arrow-right.window="next()"
    x-on:keydown.arrow-left.window="prev()"
>
    @if($testimonials->count())

        <div class="cc-container">

            {{-- Header --}}
            <div class="cc-testimonials__header">

                <div class="cc-testimonials__heading">
                    <div class="cc-testimonials__eyebrow">
                        <span></span>
                        05 / CLIENT LOVE
                    </div>

                    <h2>
                        Don't just take
                        <em>our word for it.</em>
                    </h2>
                </div>

                @if($testimonials->count() > 1)
                    <div class="cc-testimonials__controls">

                        <div class="cc-testimonials__count">
                            <strong
                                x-text="String(current + 1).padStart(2, '0')"
                            ></strong>
                            <span>/</span>
                            <span>
                                {{ str_pad($testimonials->count(), 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        <div class="cc-testimonials__arrows">
                            <button
                                type="button"
                                aria-label="Previous testimonial"
                                x-on:click="prev()"
                            >
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M15 18L9 12L15 6"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>

                            <button
                                type="button"
                                aria-label="Next testimonial"
                                x-on:click="next()"
                            >
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M9 18L15 12L9 6"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </div>

                    </div>
                @endif

            </div>


            {{-- Slider --}}
            <div class="cc-testimonial-slider">

                <div class="cc-testimonial-track">

                    @foreach($testimonials as $index => $testimonial)

                        <article
                            class="cc-testimonial-slide"
                            x-cloak
                            x-show="current === {{ $index }}"
                            x-bind:aria-hidden="current !== {{ $index }}"
                            x-transition:enter="cc-testimonial-enter"
                            x-transition:enter-start="cc-testimonial-enter-start"
                            x-transition:enter-end="cc-testimonial-enter-end"
                            x-transition:leave="cc-testimonial-leave"
                            x-transition:leave-start="cc-testimonial-leave-start"
                            x-transition:leave-end="cc-testimonial-leave-end"
                        >

                            <div class="cc-testimonial">

                                {{-- Purple ambient glow --}}
                                <div
                                    class="cc-testimonial__ambient"
                                    aria-hidden="true"
                                ></div>

                                {{-- Top line --}}
                                <div class="cc-testimonial__top">

                                    <div class="cc-testimonial__stars">
                                        @for($star = 0; $star < 5; $star++)
                                            <svg viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2.5L14.93 8.44L21.5 9.4L16.75 14.02L17.87 20.55L12 17.47L6.13 20.55L7.25 14.02L2.5 9.4L9.07 8.44L12 2.5Z"/>
                                            </svg>
                                        @endfor
                                    </div>

                                    <span>CLIENT TESTIMONIAL</span>

                                </div>


                                {{-- Quote --}}
                                <div class="cc-testimonial__quote">

                                    <div
                                        class="cc-testimonial__mark"
                                        aria-hidden="true"
                                    >“</div>

                                    <blockquote>
                                        {{ $testimonial->message }}
                                    </blockquote>

                                </div>


                                {{-- Bottom --}}
                                <div class="cc-testimonial__bottom">

                                    <div class="cc-testimonial__author">

                                        <div class="cc-testimonial__avatar">
                                            {{ strtoupper(substr($testimonial->name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $testimonial->name }}
                                            </strong>

                                            @if($testimonial->display_role)
                                                <span>
                                                    {{ $testimonial->display_role }}
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                    <div class="cc-testimonial__accent">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            </div>


            {{-- Dots --}}
            @if($testimonials->count() > 1)

                <div class="cc-testimonials__dots">

                    @foreach($testimonials as $index => $testimonial)

                        <button
                            type="button"
                            aria-label="Go to testimonial {{ $index + 1 }}"
                            x-bind:class="{ 'is-active': current === {{ $index }} }"
                            x-on:click="goTo({{ $index }})"
                        >
                            <span></span>
                        </button>

                    @endforeach

                </div>

            @endif

        </div>

    @else

        <div class="cc-container">
            <div class="cc-testimonials__empty">

                <div class="cc-testimonials__eyebrow">
                    <span></span>
                    05 / CLIENT LOVE
                </div>

                <h2>
                    What our clients
                    <em>have to say.</em>
                </h2>

                <p>
                    Testimonials will appear here once they're added.
                </p>

            </div>
        </div>

    @endif
</section>


@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }


    /* =========================================================
       ROOT
       ========================================================= */

    .cc-testimonials {
        --purple: #8b5cf6;
        --purple-bright: #a78bfa;
        --purple-soft: rgba(139, 92, 246, 0.16);

        --lime: #b9e83a;

        --white: #f7f7f4;
        --muted: rgba(247, 247, 244, 0.46);

        position: relative;
        isolation: isolate;

        padding: 90px 0 100px;

        overflow: hidden;

        color: var(--white);

        background:
            radial-gradient(
                600px 400px at 80% 45%,
                rgba(124, 58, 237, 0.12),
                transparent 70%
            ),
            radial-gradient(
                500px 350px at 15% 90%,
                rgba(139, 92, 246, 0.055),
                transparent 70%
            ),
            #060507;
    }


    .cc-testimonials::before {
        content: "";

        position: absolute;
        inset: 0;

        pointer-events: none;

        background:
            linear-gradient(
                115deg,
                transparent 0%,
                rgba(139, 92, 246, 0.035) 48%,
                transparent 70%
            );
    }


    /* =========================================================
       HEADER
       ========================================================= */

    .cc-testimonials__header {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 30px;

        margin-bottom: 42px;
    }


    .cc-testimonials__heading {
        max-width: 700px;
    }


    .cc-testimonials__eyebrow {
        display: flex;
        align-items: center;
        gap: 9px;

        margin-bottom: 17px;

        color: var(--muted);

        font-size: 9px;
        font-weight: 700;
        line-height: 1;

        letter-spacing: 0.2em;
        text-transform: uppercase;
    }


    .cc-testimonials__eyebrow span {
        width: 5px;
        height: 5px;

        border-radius: 50%;

        background: var(--lime);

        box-shadow:
            0 0 12px rgba(185, 232, 58, 0.5);
    }


    .cc-testimonials__heading h2,
    .cc-testimonials__empty h2 {
        margin: 0;

        font-size: clamp(40px, 5vw, 68px);
        font-weight: 500;

        line-height: 0.98;
        letter-spacing: -0.055em;
    }


    .cc-testimonials__heading h2 em,
    .cc-testimonials__empty h2 em {
        display: block;

        color: transparent;

        font-style: normal;

        background:
            linear-gradient(
                100deg,
                rgba(255,255,255,0.4),
                var(--purple-bright) 55%,
                rgba(185, 232, 58, 0.75)
            );

        -webkit-background-clip: text;
        background-clip: text;
    }


    /* =========================================================
       CONTROLS
       ========================================================= */

    .cc-testimonials__controls {
        display: flex;
        align-items: center;
        gap: 20px;

        flex-shrink: 0;
    }


    .cc-testimonials__count {
        display: flex;
        align-items: baseline;
        gap: 5px;

        color: rgba(255,255,255,0.22);

        font-size: 10px;
        font-weight: 600;

        letter-spacing: 0.1em;
        font-variant-numeric: tabular-nums;
    }


    .cc-testimonials__count strong {
        color: var(--purple-bright);

        font-size: 14px;
        font-weight: 600;
    }


    .cc-testimonials__arrows {
        display: flex;
        gap: 7px;
    }


    .cc-testimonials__arrows button {
        display: grid;
        place-items: center;

        width: 44px;
        height: 44px;

        padding: 0;

        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 50%;

        color: rgba(255,255,255,0.6);

        background: rgba(255,255,255,0.025);

        cursor: pointer;

        transition:
            color 450ms ease,
            background 450ms ease,
            border-color 450ms ease,
            box-shadow 450ms ease;
    }


    .cc-testimonials__arrows button svg {
        width: 17px;
        height: 17px;
    }


    .cc-testimonials__arrows button:hover {
        color: var(--white);

        border-color: rgba(139,92,246,0.45);

        background: rgba(139,92,246,0.12);

        box-shadow:
            0 0 25px rgba(139,92,246,0.12);
    }


    .cc-testimonials__arrows button:focus-visible {
        outline: none;

        border-color: var(--purple);

        box-shadow:
            0 0 0 3px rgba(139,92,246,0.15);
    }


    /* =========================================================
       SLIDER
       ========================================================= */

    .cc-testimonial-track {
        display: grid;

        width: 100%;
    }


    .cc-testimonial-slide {
        grid-area: 1 / 1;

        width: 100%;
        min-width: 0;
    }


    /* =========================================================
       CARD
       ========================================================= */

    .cc-testimonial {
        position: relative;
        isolation: isolate;

        display: flex;
        flex-direction: column;

        width: 100%;

        /*
         * Much smaller than before.
         * Content can still grow naturally.
         */
        min-height: 365px;

        padding: 34px 38px 30px;

        box-sizing: border-box;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,0.085);
        border-radius: 24px;

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,0.045),
                rgba(255,255,255,0.018) 45%,
                rgba(88, 28, 135, 0.055)
            );

        box-shadow:
            0 24px 70px rgba(0,0,0,0.25),
            inset 0 1px 0 rgba(255,255,255,0.035);

        /*
         * Only visual properties transition.
         * Absolutely NO transform here.
         */
        transition:
            border-color 500ms ease,
            background 500ms ease,
            box-shadow 500ms ease;
    }


    .cc-testimonial:hover {
        border-color: rgba(139,92,246,0.3);

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,0.05),
                rgba(139,92,246,0.025) 48%,
                rgba(88,28,135,0.09)
            );

        box-shadow:
            0 26px 75px rgba(0,0,0,0.28),
            0 0 60px rgba(109,40,217,0.045),
            inset 0 1px 0 rgba(255,255,255,0.04);
    }


    /* =========================================================
       CARD TOP ACCENT
       ========================================================= */

    .cc-testimonial::before {
        content: "";

        position: absolute;

        top: 0;
        left: 13%;
        right: 13%;

        height: 1px;

        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(139,92,246,0.75),
                rgba(185,232,58,0.45),
                transparent
            );

        opacity: 0.65;
    }


    .cc-testimonial::after {
        content: "";

        position: absolute;

        top: -120px;
        right: -100px;

        width: 260px;
        height: 260px;

        border-radius: 50%;

        background: rgba(124,58,237,0.1);

        filter: blur(70px);

        pointer-events: none;
        z-index: -1;

        transition:
            opacity 600ms ease;
    }


    .cc-testimonial:hover::after {
        opacity: 1.25;
    }


    /* =========================================================
       AMBIENT PURPLE
       ========================================================= */

    .cc-testimonial__ambient {
        position: absolute;

        bottom: -140px;
        left: 25%;

        width: 300px;
        height: 180px;

        border-radius: 50%;

        background: rgba(109,40,217,0.08);

        filter: blur(65px);

        pointer-events: none;
        z-index: -1;
    }


    /* =========================================================
       TOP
       ========================================================= */

    .cc-testimonial__top {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 32px;
    }


    .cc-testimonial__stars {
        display: flex;
        gap: 3px;

        color: var(--lime);
    }


    .cc-testimonial__stars svg {
        width: 11px;
        height: 11px;
    }


    .cc-testimonial__top > span {
        color: rgba(255,255,255,0.25);

        font-size: 8px;
        font-weight: 700;

        letter-spacing: 0.17em;
    }


    /* =========================================================
       QUOTE
       ========================================================= */

    .cc-testimonial__quote {
        position: relative;
        z-index: 2;

        max-width: 900px;
    }


    .cc-testimonial__mark {
        position: absolute;

        top: -28px;
        left: -12px;

        color: rgba(139,92,246,0.12);

        font-family: Georgia, "Times New Roman", serif;
        font-size: 105px;
        line-height: 1;

        pointer-events: none;
        user-select: none;
    }


    .cc-testimonial__quote blockquote {
        position: relative;
        z-index: 2;

        margin: 0;

        color: rgba(247,247,244,0.94);

        font-size: clamp(22px, 2.55vw, 35px);
        font-weight: 450;

        line-height: 1.22;
        letter-spacing: -0.035em;

        overflow-wrap: anywhere;
    }


    /* =========================================================
       BOTTOM / AUTHOR
       ========================================================= */

    .cc-testimonial__bottom {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 30px;

        margin-top: auto;
        padding-top: 38px;
    }


    .cc-testimonial__author {
        display: flex;
        align-items: center;
        gap: 12px;
    }


    .cc-testimonial__avatar {
        display: grid;
        place-items: center;

        width: 42px;
        height: 42px;
        flex: 0 0 42px;

        border: 1px solid rgba(139,92,246,0.32);
        border-radius: 12px;

        color: var(--purple-bright);

        background:
            linear-gradient(
                135deg,
                rgba(139,92,246,0.18),
                rgba(139,92,246,0.05)
            );

        font-size: 12px;
        font-weight: 700;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.04);
    }


    .cc-testimonial__author > div:last-child {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }


    .cc-testimonial__author strong {
        color: rgba(247,247,244,0.88);

        font-size: 12px;
        font-weight: 600;
    }


    .cc-testimonial__author span {
        color: rgba(247,247,244,0.32);

        font-size: 9px;
        font-weight: 500;

        letter-spacing: 0.08em;
        text-transform: uppercase;
    }


    /* =========================================================
       LITTLE ACCENT
       ========================================================= */

    .cc-testimonial__accent {
        display: flex;
        align-items: center;
        gap: 4px;

        padding-bottom: 4px;
    }


    .cc-testimonial__accent span {
        display: block;

        width: 4px;
        height: 4px;

        border-radius: 50%;

        background: var(--purple);

        opacity: 0.35;
    }


    .cc-testimonial__accent span:nth-child(2) {
        width: 18px;

        border-radius: 10px;

        background:
            linear-gradient(
                90deg,
                var(--purple),
                var(--lime)
            );

        opacity: 0.8;
    }


    .cc-testimonial__accent span:nth-child(3) {
        background: var(--lime);

        opacity: 0.45;
    }


    /* =========================================================
       TRANSITION
       ========================================================= */

    .cc-testimonial-enter {
        transition:
            opacity 550ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 650ms cubic-bezier(0.22, 1, 0.36, 1);

        will-change: opacity, transform;
    }


    .cc-testimonial-enter-start {
        opacity: 0;

        transform:
            translate3d(0, 10px, 0);
    }


    .cc-testimonial-enter-end {
        opacity: 1;

        transform:
            translate3d(0, 0, 0);
    }


    .cc-testimonial-leave {
        transition:
            opacity 300ms ease,
            transform 350ms ease;

        will-change: opacity, transform;
    }


    .cc-testimonial-leave-start {
        opacity: 1;

        transform:
            translate3d(0, 0, 0);
    }


    .cc-testimonial-leave-end {
        opacity: 0;

        transform:
            translate3d(0, -5px, 0);
    }


    /* =========================================================
       DOTS
       ========================================================= */

    .cc-testimonials__dots {
        display: flex;
        justify-content: center;
        align-items: center;

        gap: 5px;

        margin-top: 22px;
    }


    .cc-testimonials__dots button {
        display: grid;
        place-items: center;

        width: 22px;
        height: 16px;

        padding: 0;

        border: 0;
        background: transparent;

        cursor: pointer;
    }


    .cc-testimonials__dots button span {
        display: block;

        width: 4px;
        height: 4px;

        border-radius: 99px;

        background: rgba(255,255,255,0.2);

        transition:
            width 450ms cubic-bezier(0.22,1,0.36,1),
            background-color 450ms ease,
            box-shadow 450ms ease;
    }


    .cc-testimonials__dots button:hover span {
        background: rgba(139,92,246,0.7);
    }


    .cc-testimonials__dots button.is-active span {
        width: 18px;

        background:
            linear-gradient(
                90deg,
                var(--purple),
                var(--lime)
            );

        box-shadow:
            0 0 12px rgba(139,92,246,0.3);
    }


    /* =========================================================
       EMPTY
       ========================================================= */

    .cc-testimonials__empty {
        padding: 50px;

        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;

        background:
            radial-gradient(
                circle at 80% 20%,
                rgba(139,92,246,0.08),
                transparent 40%
            ),
            rgba(255,255,255,0.018);
    }


    .cc-testimonials__empty p {
        margin: 20px 0 0;

        color: var(--muted);

        font-size: 14px;
        line-height: 1.6;
    }


    /* =========================================================
       TABLET
       ========================================================= */

    @media (max-width: 800px) {

        .cc-testimonials {
            padding: 75px 0 80px;
        }


        .cc-testimonials__header {
            align-items: flex-start;
            flex-direction: column;

            margin-bottom: 32px;
        }


        .cc-testimonials__controls {
            width: 100%;
            justify-content: space-between;
        }


        .cc-testimonial {
            min-height: 350px;

            padding: 30px;
        }


        .cc-testimonial__quote blockquote {
            font-size: clamp(23px, 4.5vw, 31px);
        }

    }


    /* =========================================================
       MOBILE
       ========================================================= */

    @media (max-width: 560px) {

        .cc-testimonials {
            padding: 65px 0 70px;
        }


        .cc-testimonials__heading h2,
        .cc-testimonials__empty h2 {
            font-size: clamp(37px, 11vw, 52px);
        }


        .cc-testimonial {
            min-height: 0;

            padding: 27px 24px 24px;

            border-radius: 20px;
        }


        .cc-testimonial__top {
            align-items: flex-start;
            flex-direction: column;

            gap: 12px;

            margin-bottom: 27px;
        }


        .cc-testimonial__top > span {
            font-size: 7px;
        }


        .cc-testimonial__quote blockquote {
            font-size: 23px;
            line-height: 1.24;
        }


        .cc-testimonial__mark {
            top: -22px;
            left: -8px;

            font-size: 80px;
        }


        .cc-testimonial__bottom {
            align-items: flex-start;
            flex-direction: column;

            gap: 20px;

            margin-top: 38px;
            padding-top: 0;
        }


        .cc-testimonial__accent {
            display: none;
        }


        .cc-testimonials__arrows button {
            width: 42px;
            height: 42px;
        }

    }


    /* =========================================================
       REDUCED MOTION
       ========================================================= */

    @media (prefers-reduced-motion: reduce) {

        .cc-testimonial,
        .cc-testimonial::after,
        .cc-testimonial-enter,
        .cc-testimonial-leave,
        .cc-testimonials__arrows button,
        .cc-testimonials__dots button span {
            transition: none !important;
        }

    }
</style>
@endpush
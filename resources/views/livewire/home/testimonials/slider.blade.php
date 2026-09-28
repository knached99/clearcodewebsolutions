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

            this.current =
                (this.current - 1 + this.total) % this.total;
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

            {{-- =========================================================
                 HEADER
            ========================================================== --}}

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
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
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
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
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


            {{-- =========================================================
                 SLIDER
            ========================================================== --}}

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

                                {{-- Ambient glow --}}
                                <div
                                    class="cc-testimonial__glow"
                                    aria-hidden="true"
                                ></div>

                                <div
                                    class="cc-testimonial__glow cc-testimonial__glow--secondary"
                                    aria-hidden="true"
                                ></div>

                                {{-- Top --}}
                                <div class="cc-testimonial__top">

                                    <div
                                        class="cc-testimonial__stars"
                                        aria-label="{{ $testimonial->rating }} out of 5 stars"
                                    >
                                        @for($star = 1; $star <= 5; $star++)
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="{{ $star <= $testimonial->rating ? 'is-filled' : 'is-empty' }}"
                                                aria-hidden="true"
                                            >
                                                <path d="M12 2.5L14.93 8.44L21.5 9.4L16.75 14.02L17.87 20.55L12 17.47L6.13 20.55L7.25 14.02L2.5 9.4L9.07 8.44L12 2.5Z"/>
                                            </svg>
                                        @endfor
                                    </div>

                                    <span class="cc-testimonial__label">
                                        CLIENT TESTIMONIAL
                                    </span>

                                </div>


                                {{-- Quote --}}
                                <div class="cc-testimonial__quote">

                                    <div
                                        class="cc-testimonial__quote-mark"
                                        aria-hidden="true"
                                    >
                                        “
                                    </div>

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

                                        <div class="cc-testimonial__author-copy">

                                            <strong>
                                                {{ $testimonial->name }}
                                            </strong>

                                            @if($testimonial->display_role)
                                                <span style="color: #fff; font-size: 12px;">
                                                    {{ $testimonial->display_role }}
                                                </span>
                                            @endif

                                        </div>

                                    </div>

                                    <div
                                        class="cc-testimonial__badge"
                                        aria-hidden="true"
                                    >
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


            {{-- =========================================================
                 DOTS
            ========================================================== --}}

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
                    Made you look good? Give us the bragging rights.
                </p>

            </div>

        </div>

    @endif
</section>


@push('styles')

<style>

    /* =========================================================
       BASE
    ========================================================== */

    [x-cloak] {
        display: none !important;
    }

    .cc-testimonials {
        --purple: #8b5cf6;
        --purple-bright: #a78bfa;
        --purple-deep: #6d28d9;
        --purple-soft: rgba(139, 92, 246, 0.14);
        --lime: #b9e83a;
        --white: #f7f7f4;
        --muted: rgba(247, 247, 244, 0.44);

        position: relative;
        isolation: isolate;
        overflow: hidden;

        padding: 88px 0 92px;

        color: var(--white);

        background:
            radial-gradient(
                700px 440px at 82% 42%,
                rgba(124, 58, 237, 0.10),
                transparent 72%
            ),
            radial-gradient(
                520px 380px at 10% 85%,
                rgba(139, 92, 246, 0.055),
                transparent 72%
            ),
            linear-gradient(
                180deg,
                #060507 0%,
                #080609 50%,
                #060507 100%
            );
    }

    .cc-testimonials::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: 0.8;

        background:
            linear-gradient(
                112deg,
                transparent 0%,
                rgba(139, 92, 246, 0.03) 42%,
                transparent 70%
            );
    }

    .cc-testimonials::after {
        content: "";
        position: absolute;
        left: 10%;
        right: 10%;
        bottom: 0;

        height: 1px;

        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,0.05),
                transparent
            );

        pointer-events: none;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .cc-testimonials__header {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 30px;
        margin-bottom: 34px;
    }

    .cc-testimonials__heading {
        max-width: 680px;
    }

    .cc-testimonials__eyebrow {
        display: flex;
        align-items: center;
        gap: 9px;

        margin-bottom: 16px;

        color: var(--muted);

        font-size: 9px;
        font-weight: 700;
        line-height: 1;

        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    .cc-testimonials__eyebrow > span {
        width: 5px;
        height: 5px;

        flex: 0 0 5px;

        border-radius: 50%;

        background: var(--lime);

        box-shadow:
            0 0 8px rgba(185, 232, 58, 0.35),
            0 0 18px rgba(185, 232, 58, 0.18);
    }

    .cc-testimonials__heading h2,
    .cc-testimonials__empty h2 {
        margin: 0;

        font-size: clamp(38px, 4.6vw, 62px);
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
                105deg,
                rgba(255,255,255,0.42) 0%,
                var(--purple-bright) 52%,
                rgba(185,232,58,0.72) 100%
            );

        -webkit-background-clip: text;
        background-clip: text;
    }


    /* =========================================================
       CONTROLS
    ========================================================== */

    .cc-testimonials__controls {
        display: flex;
        align-items: center;
        gap: 18px;

        flex-shrink: 0;
    }

    .cc-testimonials__count {
        display: flex;
        align-items: baseline;
        gap: 5px;

        color: rgba(255,255,255,0.22);

        font-size: 9px;
        font-weight: 600;

        letter-spacing: 0.1em;

        font-variant-numeric: tabular-nums;
    }

    .cc-testimonials__count strong {
        color: var(--purple-bright);

        font-size: 13px;
        font-weight: 600;
    }

    .cc-testimonials__arrows {
        display: flex;
        gap: 6px;
    }

    .cc-testimonials__arrows button {
        display: grid;
        place-items: center;

        width: 42px;
        height: 42px;

        padding: 0;

        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 50%;

        color: rgba(255,255,255,0.58);

        background:
            rgba(255,255,255,0.018);

        cursor: pointer;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.025),
            0 8px 25px rgba(0,0,0,0.12);

        transition:
            color 450ms cubic-bezier(0.22, 1, 0.36, 1),
            background-color 450ms cubic-bezier(0.22, 1, 0.36, 1),
            border-color 450ms cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 450ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 450ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonials__arrows button svg {
        width: 16px;
        height: 16px;
    }

    .cc-testimonials__arrows button:hover {
        color: var(--white);

        border-color:
            rgba(139,92,246,0.38);

        background:
            rgba(139,92,246,0.085);

        box-shadow:
            0 10px 28px rgba(0,0,0,0.18),
            0 0 28px rgba(139,92,246,0.08);

        transform: translateY(-2px);
    }

    .cc-testimonials__arrows button:active {
        transform: translateY(0);
    }

    .cc-testimonials__arrows button:focus-visible {
        outline: none;

        border-color: var(--purple);

        box-shadow:
            0 0 0 3px rgba(139,92,246,0.14),
            0 10px 30px rgba(0,0,0,0.18);
    }


    /* =========================================================
       SLIDER
    ========================================================== */

    .cc-testimonial-slider {
        position: relative;
        z-index: 2;

        width: 100%;
    }

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
    ========================================================== */

    .cc-testimonial {
        position: relative;
        isolation: isolate;

        display: flex;
        flex-direction: column;

        width: 100%;
        min-height: 300px;

        box-sizing: border-box;

        padding: 28px 32px 26px;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,0.075);
        border-radius: 20px;

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,0.045) 0%,
                rgba(255,255,255,0.018) 42%,
                rgba(88,28,135,0.045) 100%
            );

        box-shadow:
            0 18px 55px rgba(0,0,0,0.24),
            0 2px 8px rgba(0,0,0,0.12),
            inset 0 1px 0 rgba(255,255,255,0.028);

        transform: translate3d(0, 0, 0);

        transition:
            transform 700ms cubic-bezier(0.22, 1, 0.36, 1),
            border-color 600ms cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 700ms cubic-bezier(0.22, 1, 0.36, 1),
            background 700ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial:hover {
        transform: translate3d(0, -5px, 0);

        border-color:
            rgba(139,92,246,0.22);

        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,0.055) 0%,
                rgba(255,255,255,0.022) 42%,
                rgba(88,28,135,0.075) 100%
            );

        box-shadow:
            0 28px 70px rgba(0,0,0,0.30),
            0 8px 30px rgba(0,0,0,0.16),
            0 0 55px rgba(109,40,217,0.075),
            inset 0 1px 0 rgba(255,255,255,0.04);
    }


    /* =========================================================
       CARD TOP LINE
    ========================================================== */

    .cc-testimonial::before {
        content: "";

        position: absolute;
        top: 0;
        left: 11%;
        right: 11%;

        height: 1px;

        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(139,92,246,0.68),
                rgba(185,232,58,0.38),
                transparent
            );

        opacity: 0.72;
    }

    .cc-testimonial::after {
        content: "";

        position: absolute;

        top: -85px;
        right: -70px;

        width: 210px;
        height: 210px;

        border-radius: 50%;

        background:
            rgba(109,40,217,0.11);

        filter: blur(58px);

        opacity: 0.7;

        pointer-events: none;
        z-index: -1;

        transition:
            transform 900ms cubic-bezier(0.22, 1, 0.36, 1),
            opacity 700ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial:hover::after {
        transform: translate3d(-12px, 8px, 0) scale(1.08);
        opacity: 1;
    }


    /* =========================================================
       GLOWS
    ========================================================== */

    .cc-testimonial__glow {
        position: absolute;

        left: 18%;
        bottom: -105px;

        width: 250px;
        height: 125px;

        border-radius: 50%;

        background:
            rgba(109,40,217,0.075);

        filter: blur(55px);

        pointer-events: none;

        z-index: -1;

        transition:
            transform 900ms cubic-bezier(0.22, 1, 0.36, 1),
            opacity 700ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial__glow--secondary {
        left: auto;
        right: 17%;
        bottom: -130px;

        width: 160px;
        height: 80px;

        background:
            rgba(185,232,58,0.025);
    }

    .cc-testimonial:hover .cc-testimonial__glow {
        transform: translate3d(0, -10px, 0) scale(1.06);
    }


    /* =========================================================
       TOP
    ========================================================== */

    .cc-testimonial__top {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 20px;

        margin-bottom: 25px;
    }

    .cc-testimonial__stars {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cc-testimonial__stars svg {
        width: 15px;
        height: 15px;

        flex: 0 0 15px;

        transition:
            transform 500ms cubic-bezier(0.22, 1, 0.36, 1),
            opacity 400ms ease;
    }

    .cc-testimonial__stars svg.is-filled {
        color: var(--lime);
        opacity: 0.95;
    }

    .cc-testimonial__stars svg.is-empty {
        color: rgba(255,255,255,0.12);
        opacity: 1;
    }

    .cc-testimonial:hover .cc-testimonial__stars svg.is-filled {
        transform: translateY(-1px);
    }

    .cc-testimonial__label {
        color: rgba(255,255,255,0.23);

        font-size: 7px;
        font-weight: 700;

        line-height: 1;

        letter-spacing: 0.18em;
        text-transform: uppercase;
    }


    /* =========================================================
       QUOTE
    ========================================================== */

    .cc-testimonial__quote {
        position: relative;
        z-index: 2;

        max-width: 820px;
    }

    .cc-testimonial__quote-mark {
        position: absolute;

        top: -28px;
        left: -7px;

        color: rgba(139,92,246,0.105);

        font-family:
            Georgia,
            "Times New Roman",
            serif;

        font-size: 82px;
        font-weight: 400;
        line-height: 1;

        pointer-events: none;
        user-select: none;

        transition:
            color 600ms ease,
            transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial:hover .cc-testimonial__quote-mark {
        color: rgba(139,92,246,0.15);

        transform:
            translate3d(2px, -2px, 0);
    }

    .cc-testimonial__quote blockquote {
        position: relative;
        z-index: 2;

        max-width: 760px;

        margin: 0;

        color: rgba(247,247,244,0.91);

        font-size: clamp(19px, 1.9vw, 25px);
        font-weight: 450;

        line-height: 1.38;

        letter-spacing: -0.025em;

        overflow-wrap: anywhere;
    }


    /* =========================================================
       BOTTOM
    ========================================================== */

    .cc-testimonial__bottom {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 24px;

        margin-top: auto;
        padding-top: 30px;
    }

    .cc-testimonial__author {
        display: flex;
        align-items: center;

        gap: 10px;
    }

    .cc-testimonial__avatar {
        display: grid;
        place-items: center;

        width: 38px;
        height: 38px;

        flex: 0 0 38px;

        border: 1px solid rgba(139,92,246,0.28);
        border-radius: 11px;

        color: var(--purple-bright);

        background:
            linear-gradient(
                135deg,
                rgba(139,92,246,0.17),
                rgba(139,92,246,0.035)
            );

        font-size: 11px;
        font-weight: 700;

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.04),
            0 6px 18px rgba(0,0,0,0.12);

        transition:
            transform 600ms cubic-bezier(0.22, 1, 0.36, 1),
            border-color 500ms ease,
            box-shadow 600ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial:hover .cc-testimonial__avatar {
        transform: translateY(-2px);

        border-color:
            rgba(139,92,246,0.40);

        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.04),
            0 8px 24px rgba(0,0,0,0.16),
            0 0 20px rgba(139,92,246,0.08);
    }

    .cc-testimonial__author-copy {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .cc-testimonial__author strong {
        color: rgba(247,247,244,0.88);

        font-size: 11px;
        font-weight: 600;
    }

    .cc-testimonial__author span {
        color: rgba(247,247,244,0.30);

        font-size: 8px;
        font-weight: 500;

        line-height: 1.3;

        letter-spacing: 0.07em;
        text-transform: uppercase;
    }


    /* =========================================================
       ACCENT
    ========================================================== */

    .cc-testimonial__badge {
        display: flex;
        align-items: center;
        gap: 4px;

        padding-bottom: 3px;
    }

    .cc-testimonial__badge span {
        display: block;

        width: 4px;
        height: 4px;

        border-radius: 99px;

        background: var(--purple);

        opacity: 0.32;

        transition:
            transform 600ms cubic-bezier(0.22, 1, 0.36, 1),
            opacity 500ms ease;
    }

    .cc-testimonial__badge span:nth-child(2) {
        width: 16px;

        background:
            linear-gradient(
                90deg,
                var(--purple),
                var(--lime)
            );

        opacity: 0.78;
    }

    .cc-testimonial__badge span:nth-child(3) {
        background: var(--lime);
        opacity: 0.42;
    }

    .cc-testimonial:hover .cc-testimonial__badge span:nth-child(1) {
        transform: translateX(-1px);
        opacity: 0.5;
    }

    .cc-testimonial:hover .cc-testimonial__badge span:nth-child(2) {
        transform: scaleX(1.12);
    }

    .cc-testimonial:hover .cc-testimonial__badge span:nth-child(3) {
        transform: translateX(1px);
        opacity: 0.62;
    }


    /* =========================================================
       SLIDE TRANSITIONS
    ========================================================== */

    .cc-testimonial-enter {
        transition:
            opacity 650ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 750ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonial-enter-start {
        opacity: 0;

        transform:
            translate3d(0, 16px, 0)
            scale(0.985);
    }

    .cc-testimonial-enter-end {
        opacity: 1;

        transform:
            translate3d(0, 0, 0)
            scale(1);
    }

    .cc-testimonial-leave {
        position: relative;

        transition:
            opacity 380ms cubic-bezier(0.4, 0, 1, 1),
            transform 430ms cubic-bezier(0.4, 0, 1, 1);
    }

    .cc-testimonial-leave-start {
        opacity: 1;

        transform:
            translate3d(0, 0, 0)
            scale(1);
    }

    .cc-testimonial-leave-end {
        opacity: 0;

        transform:
            translate3d(0, -8px, 0)
            scale(0.992);
    }


    /* =========================================================
       DOTS
    ========================================================== */

    .cc-testimonials__dots {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: center;

        gap: 3px;

        margin-top: 20px;
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

        border-radius: 999px;

        background: rgba(255,255,255,0.18);

        transition:
            width 500ms cubic-bezier(0.22, 1, 0.36, 1),
            background 500ms cubic-bezier(0.22, 1, 0.36, 1),
            box-shadow 500ms cubic-bezier(0.22, 1, 0.36, 1);
    }

    .cc-testimonials__dots button:hover span {
        background: rgba(139,92,246,0.7);

        box-shadow:
            0 0 10px rgba(139,92,246,0.18);
    }

    .cc-testimonials__dots button.is-active span {
        width: 17px;

        background:
            linear-gradient(
                90deg,
                var(--purple),
                var(--lime)
            );

        box-shadow:
            0 0 12px rgba(139,92,246,0.25);
    }


    /* =========================================================
       EMPTY
    ========================================================== */

    .cc-testimonials__empty {
        position: relative;
        overflow: hidden;

        padding: 42px;

        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 20px;

        background:
            radial-gradient(
                circle at 82% 20%,
                rgba(139,92,246,0.085),
                transparent 42%
            ),
            rgba(255,255,255,0.016);

        box-shadow:
            0 18px 55px rgba(0,0,0,0.20),
            inset 0 1px 0 rgba(255,255,255,0.025);
    }

    .cc-testimonials__empty p {
        margin: 18px 0 0;

        color: var(--muted);

        font-size: 13px;
        line-height: 1.6;
    }


    /* =========================================================
       TABLET
    ========================================================== */

    @media (max-width: 800px) {

        .cc-testimonials {
            padding: 72px 0 78px;
        }

        .cc-testimonials__header {
            align-items: flex-start;
            flex-direction: column;

            margin-bottom: 28px;
        }

        .cc-testimonials__controls {
            width: 100%;
            justify-content: space-between;
        }

        .cc-testimonial {
            min-height: 290px;

            padding: 26px 28px 24px;
        }

        .cc-testimonial__quote blockquote {
            font-size: clamp(18px, 3.4vw, 23px);
        }

    }


    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 560px) {

        .cc-testimonials {
            padding: 62px 0 68px;
        }

        .cc-testimonials__heading h2,
        .cc-testimonials__empty h2 {
            font-size: clamp(34px, 10.5vw, 48px);
        }

        .cc-testimonial {
            min-height: 0;

            padding: 24px 21px 21px;

            border-radius: 18px;
        }

        .cc-testimonial__top {
            align-items: flex-start;
            flex-direction: column;

            gap: 10px;

            margin-bottom: 22px;
        }

        .cc-testimonial__label {
            font-size: 6px;
        }

        .cc-testimonial__quote blockquote {
            font-size: 18px;
            line-height: 1.4;
        }

        .cc-testimonial__quote-mark {
            top: -19px;
            left: -5px;

            font-size: 68px;
        }

        .cc-testimonial__bottom {
            align-items: flex-start;
            flex-direction: column;

            gap: 16px;

            margin-top: 28px;
            padding-top: 0;
        }

        .cc-testimonial__badge {
            display: none;
        }

        .cc-testimonials__arrows button {
            width: 40px;
            height: 40px;
        }

    }


    /* =========================================================
       REDUCED MOTION
    ========================================================== */

    @media (prefers-reduced-motion: reduce) {

        .cc-testimonial,
        .cc-testimonial::after,
        .cc-testimonial__glow,
        .cc-testimonial__avatar,
        .cc-testimonial__quote-mark,
        .cc-testimonial__stars svg,
        .cc-testimonial__badge span,
        .cc-testimonial-enter,
        .cc-testimonial-leave,
        .cc-testimonials__arrows button,
        .cc-testimonials__dots button span {
            transition: none !important;
        }

    }

</style>

@endpush
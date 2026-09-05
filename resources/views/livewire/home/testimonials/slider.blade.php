<section
    id="testimonials"
    class="cc-testimonials"
    x-data="{
        current: 0,
        total: {{ $testimonials->count() }},
        autoplay: null,

        start() {
            if (this.total <= 1) {
                return;
            }

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
            if (this.total <= 1) {
                return;
            }

            this.current =
                (this.current + 1) % this.total;
        },

        prev() {
            if (this.total <= 1) {
                return;
            }

            this.current =
                (this.current - 1 + this.total) % this.total;
        },

        goTo(index) {
            this.current = index;
        }
    }"
    x-init="start()"
    x-on:mouseenter="stop()"
    x-on:mouseleave="start()"
    x-on:keydown.arrow-right.window="next()"
    x-on:keydown.arrow-left.window="prev()"
>

    <div class="cc-container">

        {{-- Header --}}
        <div class="cc-testimonials__top">

            <div>

                <span class="cc-eyebrow">
                    05 / CLIENT LOVE
                </span>

                <h2>
                    Don't just take
                    <span>our word for it.</span>
                </h2>

            </div>


            @if($testimonials->count() > 1)

                <div class="cc-testimonials__controls">

                    <button
                        type="button"
                        class="cc-testimonials__control"
                        @click="prev()"
                        aria-label="Previous testimonial"
                    >
                        ←
                    </button>

                    <button
                        type="button"
                        class="cc-testimonials__control"
                        @click="next()"
                        aria-label="Next testimonial"
                    >
                        →
                    </button>

                </div>

            @endif

        </div>


        @if($testimonials->isNotEmpty())

            {{-- Slider --}}
            <div
                class="cc-testimonial-slider"
                role="region"
                aria-label="Client testimonials"
            >

                <div class="cc-testimonial-track">

                    @foreach($testimonials as $index => $testimonial)

                        <article
                            class="cc-testimonial-slide"
                            x-show="current === {{ $index }}"
                            x-transition:enter="cc-testimonial-enter"
                            x-transition:enter-start="cc-testimonial-enter-start"
                            x-transition:enter-end="cc-testimonial-enter-end"
                            x-transition:leave="cc-testimonial-leave"
                            x-transition:leave-start="cc-testimonial-leave-start"
                            x-transition:leave-end="cc-testimonial-leave-end"
                            @if($index !== 0)
                                style="display:none"
                            @endif
                        >

                            <div class="cc-testimonial">

                                {{-- Quote --}}
                                <div class="cc-testimonial__quote">
                                    “
                                </div>


                                {{-- Rating --}}
                                <div
                                    class="cc-testimonial__rating"
                                    aria-label="{{ $testimonial->rating }} out of 5 stars"
                                >
                                    @for($star = 1; $star <= 5; $star++)

                                        <span
                                            @class([
                                                'is-active' => $testimonial->rating >= $star,
                                            ])
                                        >
                                            ★
                                        </span>

                                    @endfor
                                </div>


                                {{-- Message --}}
                                <blockquote>
                                    {{ $testimonial->message }}
                                </blockquote>


                                {{-- Author --}}
                                <div class="cc-testimonial__author">

                                    <div class="cc-testimonial__avatar">
                                        {{ $testimonial->initials }}
                                    </div>

                                    <div class="cc-testimonial__author-info">

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

                            </div>


                            {{-- Featured panel --}}
                            <div class="cc-testimonial-slide__aside">

                                <span class="cc-testimonial-slide__number">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <span class="cc-testimonial-slide__slash">
                                    /
                                </span>

                                <span>
                                    {{ str_pad($testimonials->count(), 2, '0', STR_PAD_LEFT) }}
                                </span>

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
                            @click="goTo({{ $index }})"
                            class="cc-testimonials__dot"
                            :class="{ 'is-active': current === {{ $index }} }"
                            aria-label="Show testimonial {{ $index + 1 }}"
                        ></button>

                    @endforeach

                </div>

            @endif


        @else

            {{-- Empty state --}}
            <div class="cc-testimonials__empty">

                <div class="cc-testimonials__empty-icon">
                    ★
                </div>

                <h3>
                    Be our first review.
                </h3>

                <p>
                    If you've worked with ClearCode,
                    we'd love to hear about your experience.
                </p>

                <a
                    href="#testimonial-form"
                    class="cc-testimonials__empty-link"
                >
                    Share your experience
                    <span>↗</span>
                </a>

            </div>

        @endif

    </div>

</section>


@push('styles')
<style>

.cc-testimonials {
    --lime: #ADD322;
    --purple: #7c3aed;
    --violet: #6d28d9;

    position: relative;

    padding: 130px 0;

    overflow: hidden;

    color: #fff;

    background:
        radial-gradient(
            900px circle at 10% 15%,
            rgba(124,58,237,.23),
            transparent 58%
        ),
        radial-gradient(
            850px circle at 92% 78%,
            rgba(91,33,182,.19),
            transparent 62%
        ),
        radial-gradient(
            550px circle at 50% 45%,
            rgba(139,92,246,.055),
            transparent 70%
        ),
        linear-gradient(
            135deg,
            #020104 0%,
            #08040f 25%,
            #10061b 48%,
            #08040f 72%,
            #020204 100%
        );
}


/*
|--------------------------------------------------------------------------
| Atmospheric glows
|--------------------------------------------------------------------------
*/

.cc-testimonials::before {
    content: "";

    position: absolute;

    width: 750px;
    height: 750px;

    top: -280px;
    left: -350px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(124,58,237,.13),
            rgba(109,40,217,.045) 45%,
            transparent 72%
        );

    filter: blur(100px);

    pointer-events: none;
}

.cc-testimonials::after {
    content: "";

    position: absolute;

    width: 650px;
    height: 650px;

    right: -320px;
    bottom: -300px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(91,33,182,.13),
            transparent 70%
        );

    filter: blur(100px);

    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

.cc-testimonials__top {
    position: relative;

    z-index: 1;

    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    gap: 30px;

    margin-bottom: 55px;
}

.cc-testimonials__top h2 {
    max-width: 680px;

    margin: 20px 0 0;

    color: #fff;

    font-size: clamp(44px, 6vw, 78px);

    line-height: .95;

    letter-spacing: -.06em;

    font-weight: 700;
}

.cc-testimonials__top h2 span {
    display: block;

    color: var(--lime);
}


/*
|--------------------------------------------------------------------------
| Navigation controls
|--------------------------------------------------------------------------
*/

.cc-testimonials__controls {
    display: flex;

    gap: 8px;

    flex-shrink: 0;
}

.cc-testimonials__control {
    display: grid;

    place-items: center;

    width: 48px;
    height: 48px;

    padding: 0;

    border: 1px solid rgba(255,255,255,.1);

    border-radius: 50%;

    color: rgba(255,255,255,.68);

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.035),
            rgba(255,255,255,.01)
        );

    cursor: pointer;

    font-family: inherit;

    font-size: 17px;

    transition:
        border-color .25s ease,
        color .25s ease,
        background .25s ease,
        transform .25s ease,
        box-shadow .25s ease;
}

.cc-testimonials__control:hover {
    border-color: rgba(173,211,34,.35);

    color: var(--lime);

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.07),
            rgba(124,58,237,.05)
        );

    transform: translateY(-2px);

    box-shadow:
        0 10px 30px rgba(0,0,0,.25);
}

.cc-testimonials__control:active {
    transform: translateY(0);
}


/*
|--------------------------------------------------------------------------
| Slider
|--------------------------------------------------------------------------
*/

.cc-testimonial-slider {
    position: relative;

    z-index: 1;
}

.cc-testimonial-track {
    position: relative;

    min-height: 390px;
}

.cc-testimonial-slide {
    display: grid;

    grid-template-columns: 1fr auto;

    gap: 25px;

    min-height: 390px;
}


/*
|--------------------------------------------------------------------------
| Main testimonial card
|--------------------------------------------------------------------------
*/

.cc-testimonial {
    position: relative;

    display: flex;

    flex-direction: column;

    justify-content: space-between;

    min-height: 390px;

    padding: 48px;

    overflow: hidden;

    border: 1px solid rgba(255,255,255,.08);

    border-radius: 24px;

    background:
        radial-gradient(
            600px circle at 88% 5%,
            rgba(124,58,237,.20),
            transparent 64%
        ),
        radial-gradient(
            450px circle at 5% 100%,
            rgba(91,33,182,.10),
            transparent 68%
        ),
        linear-gradient(
            145deg,
            rgba(24,9,39,.94),
            rgba(6,3,10,.97)
        );

    box-shadow:
        0 35px 110px rgba(0,0,0,.46),
        0 0 90px rgba(76,29,149,.08),
        inset 0 1px rgba(255,255,255,.045);

    transition:
        border-color .3s ease,
        box-shadow .3s ease;
}

.cc-testimonial:hover {
    border-color: rgba(139,92,246,.16);

    box-shadow:
        0 40px 120px rgba(0,0,0,.5),
        0 0 100px rgba(76,29,149,.11),
        inset 0 1px rgba(255,255,255,.05);
}


/*
|--------------------------------------------------------------------------
| Lime accent line
|--------------------------------------------------------------------------
*/

.cc-testimonial::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 125px;
    height: 2px;

    background:
        linear-gradient(
            90deg,
            var(--lime),
            rgba(173,211,34,.25),
            transparent
        );

    box-shadow:
        0 0 25px rgba(173,211,34,.3);
}


/*
|--------------------------------------------------------------------------
| Quote mark
|--------------------------------------------------------------------------
*/

.cc-testimonial__quote {
    position: absolute;

    top: 0;
    right: 30px;

    color: rgba(124,58,237,.13);

    font-family: Georgia, serif;

    font-size: 170px;

    line-height: .95;

    pointer-events: none;

    user-select: none;
}


/*
|--------------------------------------------------------------------------
| Rating
|--------------------------------------------------------------------------
*/

.cc-testimonial__rating {
    position: relative;

    z-index: 1;

    display: flex;

    gap: 3px;

    margin-bottom: 28px;
}

.cc-testimonial__rating span {
    color: rgba(255,255,255,.1);

    font-size: 13px;
}

.cc-testimonial__rating span.is-active {
    color: var(--lime);

    text-shadow:
        0 0 16px rgba(173,211,34,.2);
}


/*
|--------------------------------------------------------------------------
| Quote
|--------------------------------------------------------------------------
*/

.cc-testimonial blockquote {
    position: relative;

    z-index: 1;

    max-width: 900px;

    margin: 0;

    color: rgba(255,255,255,.91);

    font-size: clamp(24px, 3vw, 39px);

    line-height: 1.2;

    letter-spacing: -.025em;

    font-weight: 400;
}


/*
|--------------------------------------------------------------------------
| Author
|--------------------------------------------------------------------------
*/

.cc-testimonial__author {
    position: relative;

    z-index: 1;

    display: flex;

    align-items: center;

    gap: 14px;

    margin-top: 35px;
}

.cc-testimonial__avatar {
    display: grid;

    place-items: center;

    width: 50px;
    height: 50px;

    flex: 0 0 auto;

    border: 1px solid rgba(173,211,34,.22);

    border-radius: 50%;

    color: var(--lime);

    background:
        linear-gradient(
            135deg,
            rgba(173,211,34,.1),
            rgba(124,58,237,.13)
        );

    box-shadow:
        0 0 25px rgba(124,58,237,.07);

    font-size: 12px;

    font-weight: 700;
}

.cc-testimonial__author-info {
    display: flex;

    flex-direction: column;

    gap: 4px;
}

.cc-testimonial__author-info strong {
    color: rgba(255,255,255,.87);

    font-size: 13px;

    font-weight: 600;
}

.cc-testimonial__author-info span {
    color: rgba(255,255,255,.32);

    font-size: 11px;
}


/*
|--------------------------------------------------------------------------
| Slide counter
|--------------------------------------------------------------------------
*/

.cc-testimonial-slide__aside {
    display: flex;

    flex-direction: column;

    justify-content: flex-end;

    align-items: center;

    gap: 8px;

    width: 55px;

    padding-bottom: 20px;

    color: rgba(255,255,255,.18);

    font-size: 10px;
}

.cc-testimonial-slide__number {
    color: var(--lime);

    font-size: 12px;

    font-weight: 700;
}

.cc-testimonial-slide__slash {
    color: rgba(255,255,255,.1);
}


/*
|--------------------------------------------------------------------------
| Slider dots
|--------------------------------------------------------------------------
*/

.cc-testimonials__dots {
    position: relative;

    z-index: 2;

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    margin-top: 28px;
}

.cc-testimonials__dot {
    width: 7px;
    height: 7px;

    padding: 0;

    border: 0;

    border-radius: 50%;

    background: rgba(255,255,255,.14);

    cursor: pointer;

    transition:
        width .3s ease,
        background .3s ease,
        box-shadow .3s ease;
}

.cc-testimonials__dot:hover {
    background: rgba(173,211,34,.5);
}

.cc-testimonials__dot.is-active {
    width: 28px;

    border-radius: 10px;

    background: var(--lime);

    box-shadow:
        0 0 15px rgba(173,211,34,.15);
}


/*
|--------------------------------------------------------------------------
| Empty state
|--------------------------------------------------------------------------
*/

.cc-testimonials__empty {
    position: relative;

    z-index: 1;

    display: flex;

    flex-direction: column;

    align-items: center;

    padding: 75px 30px;

    text-align: center;

    border: 1px solid rgba(255,255,255,.08);

    border-radius: 24px;

    background:
        radial-gradient(
            500px circle at 50% 0%,
            rgba(124,58,237,.13),
            transparent 70%
        ),
        linear-gradient(
            145deg,
            rgba(23,9,36,.9),
            rgba(5,3,9,.96)
        );

    box-shadow:
        0 30px 90px rgba(0,0,0,.35);
}

.cc-testimonials__empty-icon {
    display: grid;

    place-items: center;

    width: 58px;
    height: 58px;

    margin-bottom: 20px;

    border: 1px solid rgba(173,211,34,.2);

    border-radius: 50%;

    color: var(--lime);

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.07),
            rgba(124,58,237,.08)
        );

    box-shadow:
        0 0 35px rgba(173,211,34,.05);
}

.cc-testimonials__empty h3 {
    margin: 0;

    color: #fff;

    font-size: 24px;

    letter-spacing: -.03em;
}

.cc-testimonials__empty p {
    max-width: 420px;

    margin: 12px 0 24px;

    color: rgba(255,255,255,.4);

    font-size: 13px;

    line-height: 1.7;
}

.cc-testimonials__empty-link {
    display: inline-flex;

    align-items: center;

    gap: 10px;

    color: var(--lime);

    font-size: 12px;

    font-weight: 600;

    text-decoration: none;
}

.cc-testimonials__empty-link span {
    transition:
        transform .2s ease;
}

.cc-testimonials__empty-link:hover span {
    transform: translate(3px, -3px);
}


/*
|--------------------------------------------------------------------------
| Slider transitions
|--------------------------------------------------------------------------
*/

.cc-testimonial-enter {
    transition:
        opacity .4s ease,
        transform .4s cubic-bezier(.2,.7,.2,1);
}

.cc-testimonial-enter-start {
    opacity: 0;

    transform:
        translateX(35px)
        scale(.985);
}

.cc-testimonial-enter-end {
    opacity: 1;

    transform:
        translateX(0)
        scale(1);
}

.cc-testimonial-leave {
    transition:
        opacity .25s ease,
        transform .25s ease;
}

.cc-testimonial-leave-start {
    opacity: 1;

    transform:
        translateX(0);
}

.cc-testimonial-leave-end {
    opacity: 0;

    transform:
        translateX(-20px);
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {

    .cc-testimonials {
        padding: 100px 0;
    }

    .cc-testimonials__top {
        align-items: flex-start;

        flex-direction: column;

        margin-bottom: 40px;
    }

    .cc-testimonial-slide {
        grid-template-columns: 1fr;
    }

    .cc-testimonial-slide__aside {
        display: none;
    }

}

@media (max-width: 700px) {

    .cc-testimonials {
        padding: 85px 0;
    }

    .cc-testimonial {
        min-height: 360px;

        padding: 35px;

        border-radius: 20px;
    }

    .cc-testimonial blockquote {
        font-size: 27px;
    }

}

@media (max-width: 600px) {

    .cc-testimonials {
        padding: 70px 0;
    }

    .cc-testimonials__top h2 {
        font-size: 43px;
    }

    .cc-testimonials__controls {
        width: 100%;
    }

    .cc-testimonials__control {
        width: 44px;
        height: 44px;
    }

    .cc-testimonial {
        min-height: 390px;

        padding: 27px;

        border-radius: 18px;
    }

    .cc-testimonial blockquote {
        font-size: 23px;

        line-height: 1.25;
    }

    .cc-testimonial__quote {
        right: 15px;

        font-size: 105px;
    }

    .cc-testimonial__author {
        margin-top: 30px;
    }

}

@media (prefers-reduced-motion: reduce) {

    .cc-testimonials *,
    .cc-testimonials *::before,
    .cc-testimonials *::after {
        transition: none !important;
        animation: none !important;
    }

}

</style>
@endpush
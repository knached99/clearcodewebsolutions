<section id="testimonial-form" class="cc-testimonial-submit">

    <div class="cc-testimonial-submit__orb"></div>

    <div class="cc-container">

        <div class="cc-testimonial-submit__inner">

            {{-- Copy --}}
            <div class="cc-testimonial-submit__copy">

                <span class="cc-eyebrow">
                    07 / YOUR EXPERIENCE
                </span>

                <h2>
                    Worked with us?
                    <span>Tell us about it.</span>
                </h2>

                <p>
                    Your feedback helps us improve and helps future
                    clients know what it's like to work with ClearCode.
                </p>

                <div class="cc-testimonial-submit__note">
                    <span class="cc-testimonial-submit__note-icon">
                        ★
                    </span>

                    <span>
                        Every testimonial is reviewed before
                        appearing on our website.
                    </span>
                </div>

            </div>


            {{-- Form --}}
            <form
                wire:submit="submit"
                class="cc-testimonial-form"
            >

                {{-- Success --}}
                @if($submitted)

                    <div class="cc-form-status cc-form-status--success">

                        <div class="cc-form-status__icon">
                            ✓
                        </div>

                        <div>
                            <strong>Thank you.</strong>

                            <span>
                                Your testimonial has been submitted
                                and is awaiting review.
                            </span>
                        </div>

                    </div>

                @endif


                {{-- Errors --}}
                @if($errors->any())

                    <div class="cc-form-status cc-form-status--error">

                        <div class="cc-form-status__icon">
                            !
                        </div>

                        <div>
                            <strong>Something needs attention.</strong>

                            <span>
                                Please check the fields below and try again.
                            </span>
                        </div>

                    </div>

                @endif


                {{-- Honeypot --}}
                <div
                    class="cc-testimonial-honeypot"
                    aria-hidden="true"
                >
                    <label for="testimonial-website">
                        Website
                    </label>

                    <input
                        id="testimonial-website"
                        type="text"
                        wire:model="website"
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>


                {{-- Name / Email --}}
                <div class="cc-form-row">

                    <div class="cc-form-field">

                        <label for="testimonial-name">
                            Your name
                        </label>

                        <div class="cc-testimonial-input-wrap">

                            <input
                                id="testimonial-name"
                                type="text"
                                wire:model.blur="name"
                                placeholder="John Doe"
                                autocomplete="name"
                                @class([
                                    'cc-input-error' => $errors->has('name'),
                                    'cc-input-valid' => !$errors->has('name') && filled($name),
                                ])
                            >

                            <div class="cc-input-line"></div>

                        </div>

                        @error('name')
                            <span class="cc-form-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>


                    <div class="cc-form-field">

                        <label for="testimonial-email">
                            Email
                            <span class="cc-optional">
                                optional
                            </span>
                        </label>

                        <div class="cc-testimonial-input-wrap">

                            <input
                                id="testimonial-email"
                                type="email"
                                wire:model.blur="email"
                                placeholder="you@company.com"
                                autocomplete="email"
                                @class([
                                    'cc-input-error' => $errors->has('email'),
                                    'cc-input-valid' => !$errors->has('email') && filled($email),
                                ])
                            >

                            <div class="cc-input-line"></div>

                        </div>

                        @error('email')
                            <span class="cc-form-error">
                                {{ $message }}
                            </span>
                        @enderror

                    </div>

                </div>


                {{-- Company / Role --}}
                <div class="cc-form-row">

                    <div class="cc-form-field">

                        <label for="testimonial-company">
                            Company
                            <span class="cc-optional">
                                optional
                            </span>
                        </label>

                        <div class="cc-testimonial-input-wrap">

                            <input
                                id="testimonial-company"
                                type="text"
                                wire:model.blur="company"
                                placeholder="Your company"
                                autocomplete="organization"
                            >

                            <div class="cc-input-line"></div>

                        </div>

                    </div>


                    <div class="cc-form-field">

                        <label for="testimonial-role">
                            Your role
                            <span class="cc-optional">
                                optional
                            </span>
                        </label>

                        <div class="cc-testimonial-input-wrap">

                            <input
                                id="testimonial-role"
                                type="text"
                                wire:model.blur="role"
                                placeholder="Founder, CEO, Marketing Manager..."
                            >

                            <div class="cc-input-line"></div>

                        </div>

                    </div>

                </div>


                {{-- Rating --}}
                <div class="cc-form-field">
                    <label>
                        How would you rate your experience?
                    </label>

                    <div
                        class="cc-rating"
                        role="radiogroup"
                        aria-label="Rating"
                    >
                        @for($star = 1; $star <= 5; $star++)
                            <button
                                type="button"
                                wire:click="setRating({{ $star }})"
                                wire:key="testimonial-rating-star-{{ $star }}"
                                class="cc-rating__star {{ $rating >= $star ? 'is-active' : '' }}"
                                role="radio"
                                aria-label="{{ $star }} star{{ $star > 1 ? 's' : '' }}"
                                aria-checked="{{ $rating === $star ? 'true' : 'false' }}"
                            >
                                <span>★</span>
                            </button>
                        @endfor

                        <span class="cc-rating__label">
                            {{ $rating }}/5
                        </span>
                    </div>

                    @error('rating')
                        <span class="cc-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Message --}}
                <div class="cc-form-field">

                    <label for="testimonial-message">
                        Your experience
                    </label>

                    <div class="cc-testimonial-input-wrap">

                        <textarea
                            id="testimonial-message"
                            wire:model.blur="message"
                            rows="6"
                            maxlength="2000"
                            placeholder="Tell us what it was like working with ClearCode..."
                            @class([
                                'cc-input-error' => $errors->has('message'),
                                'cc-input-valid' => !$errors->has('message') && filled($message),
                            ])
                        ></textarea>

                        <div class="cc-input-line"></div>

                    </div>

                    @error('message')
                        <span class="cc-form-error">
                            {{ $message }}
                        </span>
                    @enderror

                </div>


                {{-- Form-level error --}}
                @error('form')

                    <span class="cc-form-error cc-form-error--form">
                        {{ $message }}
                    </span>

                @enderror


                {{-- Submit --}}
                <button
                    type="submit"
                    class="cc-testimonial-form__submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >

                    <span wire:loading.remove wire:target="submit">
                        Submit testimonial
                    </span>

                    <span wire:loading.remove wire:target="submit">
                        ↗
                    </span>

                    <span
                        wire:loading
                        wire:target="submit"
                        class="cc-submit-spinner"
                    >
                        <i></i>
                    </span>

                    <span
                        wire:loading
                        wire:target="submit"
                    >
                        Submitting...
                    </span>

                </button>

                <p class="cc-testimonial-form__privacy">
                    Your email is never displayed publicly.
                </p>

            </form>

        </div>

    </div>

</section>


@push('styles')
<style>

.cc-testimonial-submit {
    --lime: #ADD322;
    --purple: #7c3aed;
    --violet: #6d28d9;
    --black: #020104;

    position: relative;
    padding: 130px 0;
    overflow: hidden;

    color: #fff;

    background:
        radial-gradient(
            900px circle at 88% 12%,
            rgba(124, 58, 237, .24),
            transparent 58%
        ),
        radial-gradient(
            700px circle at 5% 88%,
            rgba(91, 33, 182, .17),
            transparent 62%
        ),
        radial-gradient(
            550px circle at 50% 48%,
            rgba(139, 92, 246, .055),
            transparent 70%
        ),
        linear-gradient(
            135deg,
            #020104 0%,
            #08040f 25%,
            #11071d 48%,
            #08040f 72%,
            #020204 100%
        );
}


/*
|--------------------------------------------------------------------------
| Ambient background
|--------------------------------------------------------------------------
*/

.cc-testimonial-submit::before {
    content: "";

    position: absolute;

    width: 700px;
    height: 700px;

    top: 5%;
    left: -350px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(109, 40, 217, .11) 0%,
            rgba(91, 33, 182, .05) 45%,
            transparent 72%
        );

    filter: blur(100px);

    pointer-events: none;
}

.cc-testimonial-submit::after {
    content: "";

    position: absolute;

    width: 500px;
    height: 500px;

    right: -250px;
    bottom: -220px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(124, 58, 237, .12),
            transparent 70%
        );

    filter: blur(100px);

    pointer-events: none;
}

.cc-testimonial-submit__orb {
    position: absolute;

    top: 4%;
    right: -180px;

    width: 550px;
    height: 550px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(124, 58, 237, .18) 0%,
            rgba(91, 33, 182, .08) 45%,
            transparent 72%
        );

    filter: blur(70px);

    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| Main layout
|--------------------------------------------------------------------------
*/

.cc-testimonial-submit__inner {
    position: relative;
    z-index: 1;

    display: grid;

    grid-template-columns: .85fr 1.15fr;

    gap: 90px;

    align-items: start;
}

.cc-testimonial-submit__copy {
    padding-top: 20px;
}

.cc-testimonial-submit__copy h2 {
    max-width: 560px;

    margin: 22px 0;

    color: #fff;

    font-size: clamp(42px, 5vw, 70px);

    line-height: .98;

    letter-spacing: -.055em;

    font-weight: 700;
}

.cc-testimonial-submit__copy h2 span {
    display: block;

    color: var(--lime);
}

.cc-testimonial-submit__copy p {
    max-width: 470px;

    margin: 0;

    color: rgba(255,255,255,.48);

    font-size: 16px;

    line-height: 1.8;
}


/*
|--------------------------------------------------------------------------
| Review note
|--------------------------------------------------------------------------
*/

.cc-testimonial-submit__note {
    display: flex;

    align-items: center;

    gap: 12px;

    max-width: 440px;

    margin-top: 34px;

    color: rgba(255,255,255,.34);

    font-size: 12px;

    line-height: 1.6;
}

.cc-testimonial-submit__note-icon {
    display: grid;

    place-items: center;

    flex: 0 0 auto;

    width: 36px;
    height: 36px;

    border: 1px solid rgba(173,211,34,.22);

    border-radius: 50%;

    color: var(--lime);

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.08),
            rgba(124,58,237,.06)
        );

    box-shadow:
        0 0 25px rgba(173,211,34,.05);
}


/*
|--------------------------------------------------------------------------
| Form card
|--------------------------------------------------------------------------
*/

.cc-testimonial-form {
    position: relative;

    padding: 34px;

    overflow: hidden;

    border: 1px solid rgba(255,255,255,.08);

    border-radius: 24px;

    background:
        radial-gradient(
            600px circle at 100% 0%,
            rgba(124,58,237,.17),
            transparent 62%
        ),
        radial-gradient(
            450px circle at 0% 100%,
            rgba(91,33,182,.08),
            transparent 65%
        ),
        linear-gradient(
            145deg,
            rgba(23, 9, 36, .94),
            rgba(5, 3, 9, .97)
        );

    box-shadow:
        0 35px 110px rgba(0,0,0,.48),
        0 0 80px rgba(76,29,149,.08),
        inset 0 1px rgba(255,255,255,.045);
}

.cc-testimonial-form::before {
    content: "";

    position: absolute;

    top: 0;
    left: 8%;

    width: 84%;
    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(173,211,34,.35),
            rgba(124,58,237,.25),
            transparent
        );
}


/*
|--------------------------------------------------------------------------
| Form rows
|--------------------------------------------------------------------------
*/

.cc-form-row {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;
}

.cc-form-field {
    position: relative;

    margin-bottom: 22px;
}

.cc-form-field label {
    display: flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 9px;

    color: rgba(255,255,255,.72);

    font-size: 12px;

    font-weight: 600;

    letter-spacing: .01em;
}

.cc-optional {
    color: rgba(255,255,255,.22);

    font-size: 10px;

    font-weight: 400;
}


/*
|--------------------------------------------------------------------------
| Inputs
|--------------------------------------------------------------------------
*/

.cc-testimonial-input-wrap {
    position: relative;
}

.cc-testimonial-input-wrap input,
.cc-testimonial-input-wrap textarea {
    display: block;

    width: 100%;

    padding: 15px 16px;

    border: 1px solid rgba(255,255,255,.075);

    border-radius: 12px;

    outline: none;

    color: #fff;

    background:
        linear-gradient(
            145deg,
            rgba(35,15,52,.55),
            rgba(5,3,9,.74)
        );

    font-family: inherit;

    font-size: 14px;

    transition:
        border-color .25s ease,
        background .25s ease,
        box-shadow .25s ease,
        transform .25s ease;
}

.cc-testimonial-input-wrap textarea {
    min-height: 145px;

    resize: vertical;

    line-height: 1.65;
}

.cc-testimonial-input-wrap input:hover,
.cc-testimonial-input-wrap textarea:hover {
    border-color: rgba(139,92,246,.25);

    background:
        linear-gradient(
            145deg,
            rgba(42,18,62,.65),
            rgba(7,4,12,.8)
        );
}

.cc-testimonial-input-wrap input:focus,
.cc-testimonial-input-wrap textarea:focus {
    border-color: rgba(173,211,34,.42);

    background:
        linear-gradient(
            145deg,
            rgba(35,14,52,.72),
            rgba(5,3,9,.88)
        );

    box-shadow:
        0 0 0 3px rgba(173,211,34,.04),
        0 15px 45px rgba(0,0,0,.25),
        0 0 35px rgba(124,58,237,.07);
}

.cc-testimonial-input-wrap input::placeholder,
.cc-testimonial-input-wrap textarea::placeholder {
    color: rgba(255,255,255,.19);
}


/*
|--------------------------------------------------------------------------
| Animated lime input line
|--------------------------------------------------------------------------
*/

.cc-input-line {
    position: absolute;

    left: 14px;
    right: 14px;
    bottom: 0;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            var(--lime),
            transparent
        );

    transform: scaleX(0);

    transform-origin: center;

    transition: transform .3s ease;

    pointer-events: none;
}

.cc-testimonial-input-wrap:focus-within .cc-input-line {
    transform: scaleX(1);
}


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

.cc-input-error {
    border-color: rgba(239,68,68,.55) !important;

    box-shadow:
        0 0 0 3px rgba(239,68,68,.035) !important;
}

.cc-input-valid {
    border-color: rgba(173,211,34,.20) !important;
}

.cc-form-error {
    display: block;

    margin-top: 7px;

    color: #f87171;

    font-size: 11px;

    line-height: 1.5;
}

.cc-form-error--form {
    margin-bottom: 18px;
}


/* --------------------------------------------------------------------------
| Rating
| -------------------------------------------------------------------------- */

.cc-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}

.cc-rating__star {
    appearance: none;
    -webkit-appearance: none;

    display: grid;
    place-items: center;

    width: 40px;
    height: 40px;

    margin: 0;
    padding: 0;

    border: 1px solid rgba(255,255,255,.07);
    border-radius: 10px;

    outline: none;

    color: rgba(255,255,255,.17);

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.035),
            rgba(255,255,255,.01)
        );

    cursor: pointer;

    font-family: inherit;
    font-size: 19px;
    line-height: 1;

    transition:
        color .2s ease,
        border-color .2s ease,
        background .2s ease,
        transform .2s ease,
        box-shadow .2s ease;
}

.cc-rating__star span {
    display: block;

    color: inherit;

    transform: translateY(-1px);

    transition:
        color .2s ease,
        transform .2s ease,
        filter .2s ease;
}

.cc-rating__star:hover {
    border-color: rgba(173,211,34,.35);

    color: #ADD322;

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.10),
            rgba(124,58,237,.07)
        );

    transform: translateY(-2px);

    box-shadow:
        0 8px 25px rgba(173,211,34,.08);
}

.cc-rating__star:hover span {
    transform: translateY(-1px) scale(1.08);
}

.cc-rating__star.is-active {
    border-color: rgba(173,211,34,.32);

    color: #ADD322;

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.13),
            rgba(124,58,237,.08)
        );

    box-shadow:
        0 0 18px rgba(173,211,34,.08),
        inset 0 0 12px rgba(173,211,34,.025);
}

.cc-rating__star.is-active span {
    color: #ADD322;

    filter:
        drop-shadow(0 0 5px rgba(173,211,34,.28));
}

.cc-rating__star:focus-visible {
    border-color: rgba(173,211,34,.55);

    box-shadow:
        0 0 0 3px rgba(173,211,34,.10),
        0 0 25px rgba(173,211,34,.08);
}

.cc-rating__star:active {
    transform: translateY(0) scale(.94);
}

.cc-rating__label {
    margin-left: 9px;

    min-width: 30px;

    color: rgba(255,255,255,.38);

    font-size: 11px;
    font-weight: 600;

    transition:
        color .2s ease;
}

.cc-rating:has(.cc-rating__star.is-active) .cc-rating__label {
    color: rgba(173,211,34,.65);
}

/*
|--------------------------------------------------------------------------
| Submit button
|--------------------------------------------------------------------------
*/

.cc-testimonial-form__submit {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 12px;

    width: 100%;

    min-height: 56px;

    padding: 0 22px;

    border: 0;

    border-radius: 12px;

    color: #050505;

    background:
        linear-gradient(
            135deg,
            #ADD322 0%,
            #b8df29 100%
        );

    font-family: inherit;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

    box-shadow:
        0 12px 35px rgba(173,211,34,.12);

    transition:
        transform .25s ease,
        box-shadow .25s ease,
        background .25s ease;
}

.cc-testimonial-form__submit:hover {
    transform: translateY(-2px);

    box-shadow:
        0 18px 50px rgba(173,211,34,.2);
}

.cc-testimonial-form__submit:active {
    transform: translateY(0);
}

.cc-testimonial-form__submit:disabled {
    cursor: wait;

    opacity: .6;

    transform: none;
}


/*
|--------------------------------------------------------------------------
| Spinner
|--------------------------------------------------------------------------
*/

.cc-submit-spinner {
    display: inline-flex;

    align-items: center;

    justify-content: center;
}

.cc-submit-spinner i {
    display: block;

    width: 15px;
    height: 15px;

    border: 2px solid rgba(0,0,0,.2);

    border-top-color: #050505;

    border-radius: 50%;

    animation:
        cc-testimonial-spin .7s linear infinite;
}

@keyframes cc-testimonial-spin {
    to {
        transform: rotate(360deg);
    }
}


/*
|--------------------------------------------------------------------------
| Status messages
|--------------------------------------------------------------------------
*/

.cc-form-status {
    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 24px;

    padding: 15px 16px;

    border-radius: 14px;

    font-size: 12px;
}

.cc-form-status__icon {
    display: grid;

    place-items: center;

    width: 34px;
    height: 34px;

    flex: 0 0 auto;

    border-radius: 50%;

    font-weight: 700;
}

.cc-form-status strong {
    display: block;

    margin-bottom: 3px;

    font-size: 12px;
}

.cc-form-status span {
    display: block;

    color: rgba(255,255,255,.4);

    font-size: 11px;

    line-height: 1.5;
}

.cc-form-status--success {
    border: 1px solid rgba(173,211,34,.15);

    background:
        linear-gradient(
            145deg,
            rgba(173,211,34,.06),
            rgba(173,211,34,.015)
        );
}

.cc-form-status--success .cc-form-status__icon {
    color: var(--lime);

    background: rgba(173,211,34,.08);
}

.cc-form-status--error {
    border: 1px solid rgba(239,68,68,.15);

    background:
        linear-gradient(
            145deg,
            rgba(239,68,68,.055),
            rgba(239,68,68,.012)
        );
}

.cc-form-status--error .cc-form-status__icon {
    color: #f87171;

    background: rgba(239,68,68,.08);
}


/*
|--------------------------------------------------------------------------
| Privacy text
|--------------------------------------------------------------------------
*/

.cc-testimonial-form__privacy {
    margin: 13px 0 0;

    text-align: center;

    color: rgba(255,255,255,.2);

    font-size: 10px;

    line-height: 1.5;
}


/*
|--------------------------------------------------------------------------
| Honeypot
|--------------------------------------------------------------------------
*/

.cc-testimonial-honeypot {
    position: absolute !important;

    width: 1px !important;
    height: 1px !important;

    overflow: hidden !important;

    opacity: 0 !important;

    pointer-events: none !important;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 1000px) {

    .cc-testimonial-submit__inner {
        grid-template-columns: 1fr;

        gap: 55px;
    }

    .cc-testimonial-submit__copy {
        padding-top: 0;
    }

    .cc-testimonial-submit__copy p {
        max-width: 600px;
    }

}

@media (max-width: 700px) {

    .cc-testimonial-submit {
        padding: 90px 0;
    }

    .cc-testimonial-form {
        padding: 25px;

        border-radius: 20px;
    }

}

@media (max-width: 600px) {

    .cc-testimonial-submit {
        padding: 70px 0;
    }

    .cc-testimonial-submit__inner {
        gap: 38px;
    }

    .cc-testimonial-submit__copy h2 {
        font-size: 43px;
    }

    .cc-form-row {
        grid-template-columns: 1fr;

        gap: 0;
    }

    .cc-testimonial-form {
        padding: 20px;

        border-radius: 18px;
    }

    .cc-rating__star {
        width: 38px;
        height: 38px;
    }

}

@media (prefers-reduced-motion: reduce) {

    .cc-testimonial-form *,
    .cc-testimonial-submit * {
        transition: none !important;
        animation: none !important;
    }

}

</style>
@endpush
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
            <form wire:submit="prepareSubmit" class="cc-testimonial-form">

                {{-- Success --}}
                @if ($submitted)
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
                @if ($errors->any())
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
                @honeypot

                {{-- Name / Email --}}
                <div class="cc-form-row">

                    <div class="cc-form-field">

                        <label for="testimonial-name">
                            Your name
                        </label>

                        <div class="cc-testimonial-input-wrap">

                            <input id="testimonial-name" type="text" wire:model.blur="name" placeholder="John Doe"
                                autocomplete="name" @class([
                                    'cc-input-error' => $errors->has('name'),
                                    'cc-input-valid' => !$errors->has('name') && filled($name),
                                ])>

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

                            <input id="testimonial-email" type="email" wire:model.blur="email"
                                placeholder="you@company.com" autocomplete="email" @class([
                                    'cc-input-error' => $errors->has('email'),
                                    'cc-input-valid' => !$errors->has('email') && filled($email),
                                ])>

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

                            <input id="testimonial-company" type="text" wire:model.blur="company"
                                placeholder="Your company" autocomplete="organization">

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

                            <input id="testimonial-role" type="text" wire:model.blur="role"
                                placeholder="Founder, CEO, Marketing Manager...">

                            <div class="cc-input-line"></div>

                        </div>

                    </div>

                </div>


                {{-- Rating --}}
                <div class="cc-form-field">
                    <label>
                        How would you rate your experience?
                    </label>

                    <div class="cc-rating" role="radiogroup" aria-label="Rating">
                        @for ($star = 1; $star <= 5; $star++)
                            <button type="button" wire:click="setRating({{ $star }})"
                                wire:key="testimonial-rating-star-{{ $star }}"
                                class="cc-rating__star {{ $rating >= $star ? 'is-active' : '' }}" role="radio"
                                aria-label="{{ $star }} star{{ $star > 1 ? 's' : '' }}"
                                aria-checked="{{ $rating === $star ? 'true' : 'false' }}">
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

                        <textarea id="testimonial-message" wire:model.blur="message" rows="6" maxlength="2000"
                            placeholder="Tell us what it was like working with ClearCode..." @class([
                                'cc-input-error' => $errors->has('message'),
                                'cc-input-valid' => !$errors->has('message') && filled($message),
                            ])></textarea>

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

                @error('recaptchaToken')
                    <span class="cc-form-error cc-form-error--form">
                        {{ $message }}
                    </span>
                @enderror


                {{-- Submit --}}
                <button type="submit" class="cc-testimonial-form__submit" wire:loading.attr="disabled"
                    wire:target="prepareSubmit,submit">

                    <span wire:loading.remove wire:target="prepareSubmit,submit">
                        Submit testimonial
                    </span>

                    <span wire:loading.remove wire:target="prepareSubmit,submit">
                        ↗
                    </span>

                    <span wire:loading wire:target="prepareSubmit,submit" class="cc-submit-spinner">
                        <i></i>
                    </span>

                    <span wire:loading wire:target="prepareSubmit,submit">
                        Submitting...
                    </span>

                </button>

                <p class="cc-testimonial-form__privacy">
                    Your email is never displayed publicly.
                </p>
                
                <p class="cc-testimonial-form__recaptcha">
                    This site is protected by reCAPTCHA and the Google 
                    <a href="https://policies.google.com/privacy">
                    Privacy Policy
                    </a>
                    and
                    <a href="https://policies.google.com/terms">
                    Terms of Service
                    </a>
                    apply.
                </p>

            </form>

        </div>

    </div>

</section>

@once
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.google_recaptcha.site_key') }}" async
        defer></script>
@endonce

@script
    <script>
        $wire.on(
            'execute-testimonial-recaptcha',
            async () => {

                try {

                    /*
                     * Make sure Google's reCAPTCHA library
                     * has loaded.
                     */
                    if (
                        typeof grecaptcha === 'undefined'
                    ) {
                        $wire.addError(
                            'form',
                            'Security verification is still loading. Please try again.'
                        );

                        return;
                    }

                    /*
                     * Wait for reCAPTCHA to be ready.
                     */
                    await new Promise((resolve) => {

                        grecaptcha.ready(
                            resolve
                        );

                    });

                    /*
                     * Generate a fresh token immediately
                     * before the testimonial is submitted.
                     */
                    const token =
                        await grecaptcha.execute(
                            '{{ config('services.google_recaptcha.site_key') }}', {
                                action: 'testimonial_submit'
                            }
                        );

                    /*
                     * Send the token to Livewire.
                     */
                    await $wire.call(
                        'submit',
                        token
                    );

                } catch (error) {

                    console.error(
                        'reCAPTCHA error:',
                        error
                    );

                    $wire.addError(
                        'form',
                        'Security verification failed. Please try again.'
                    );

                }

            }
        );
    </script>
@endscript

@push('styles')
    <style>
        /* ==========================================================================
                               TESTIMONIAL SECTION
                               ========================================================================== */

        .cc-testimonial-submit {
            --purple: #7c3aed;
            --purple-light: #a855f7;
            --purple-dark: #4c1d95;
            --black: #020104;

            position: relative;
            padding: 130px 0;
            overflow: hidden;
            color: #fff;

            background:
                radial-gradient(850px circle at 88% 10%,
                    rgba(124, 58, 237, .16),
                    transparent 62%),
                radial-gradient(650px circle at 5% 90%,
                    rgba(91, 33, 182, .13),
                    transparent 65%),
                linear-gradient(135deg,
                    #020104 0%,
                    #07040d 28%,
                    #0d0617 50%,
                    #07040d 72%,
                    #020104 100%);
        }


        /* ==========================================================================
                               AMBIENT LIGHT
                               ========================================================================== */

        .cc-testimonial-submit::before {
            content: "";
            position: absolute;
            width: 650px;
            height: 650px;
            top: -220px;
            left: -260px;
            border-radius: 50%;

            background:
                radial-gradient(circle,
                    rgba(124, 58, 237, .12) 0%,
                    rgba(91, 33, 182, .05) 45%,
                    transparent 72%);

            filter: blur(90px);
            pointer-events: none;
        }

        .cc-testimonial-submit::after {
            content: "";
            position: absolute;
            width: 550px;
            height: 550px;
            right: -260px;
            bottom: -260px;
            border-radius: 50%;

            background:
                radial-gradient(circle,
                    rgba(124, 58, 237, .12),
                    transparent 70%);

            filter: blur(90px);
            pointer-events: none;
        }


        /* ==========================================================================
                               ORB
                               ========================================================================== */

        .cc-testimonial-submit__orb {
            position: absolute;
            top: 2%;
            right: -220px;
            width: 600px;
            height: 600px;
            border-radius: 50%;

            background:
                radial-gradient(circle,
                    rgba(124, 58, 237, .13) 0%,
                    rgba(91, 33, 182, .06) 45%,
                    transparent 72%);

            filter: blur(75px);
            pointer-events: none;
        }


        /* ==========================================================================
                               MAIN LAYOUT
                               ========================================================================== */

        .cc-testimonial-submit__inner {
            position: relative;
            z-index: 1;

            display: grid;
            grid-template-columns: .85fr 1.15fr;

            gap: 80px;
            align-items: center;
        }


        /* ==========================================================================
                               LEFT COPY
                               ========================================================================== */

        .cc-testimonial-submit__copy {
            padding-top: 10px;
        }

        .cc-testimonial-submit__copy h2 {
            max-width: 620px;

            margin: 22px 0;

            color: #fff;

            font-size: clamp(42px, 5vw, 70px);
            line-height: .98;
            letter-spacing: -.055em;
            font-weight: 700;
        }

        /*
                               Keeps "Worked with us?" together on one line.
                            */
        .cc-testimonial-submit__copy h2 {
            white-space: nowrap;
        }

        .cc-testimonial-submit__copy h2 span {
            display: block;
            color: #a855f7;
        }

        .cc-testimonial-submit__copy p {
            max-width: 500px;

            margin: 0;

            color: rgba(255, 255, 255, .52);

            font-size: 16px;
            line-height: 1.8;
        }


        /* ==========================================================================
                               EYEBROW
                               ========================================================================== */

        .cc-eyebrow {
            display: inline-block;

            color: rgba(168, 85, 247, .85);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
        }


        /* ==========================================================================
                               REVIEW NOTE
                               ========================================================================== */

        .cc-testimonial-submit__note {
            display: flex;
            align-items: center;
            gap: 12px;

            max-width: 440px;
            margin-top: 34px;

            color: rgba(255, 255, 255, .35);

            font-size: 12px;
            line-height: 1.6;
        }

        .cc-testimonial-submit__note-icon {
            display: grid;
            place-items: center;

            flex: 0 0 auto;

            width: 36px;
            height: 36px;

            border: 1px solid rgba(168, 85, 247, .25);
            border-radius: 50%;

            color: #a855f7;

            background:
                linear-gradient(145deg,
                    rgba(124, 58, 237, .12),
                    rgba(124, 58, 237, .035));

            box-shadow:
                0 0 25px rgba(124, 58, 237, .08);
        }


        /* ==========================================================================
                               FORM CARD
                               ========================================================================== */

        .cc-testimonial-form {
            position: relative;

            padding: 36px;

            overflow: hidden;

            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 22px;

            background:
                radial-gradient(600px circle at 100% 0%,
                    rgba(124, 58, 237, .13),
                    transparent 62%),
                radial-gradient(450px circle at 0% 100%,
                    rgba(91, 33, 182, .07),
                    transparent 65%),
                linear-gradient(145deg,
                    rgba(19, 8, 30, .94),
                    rgba(5, 3, 9, .97));

            box-shadow:
                0 35px 110px rgba(0, 0, 0, .48),
                0 0 80px rgba(76, 29, 149, .08),
                inset 0 1px rgba(255, 255, 255, .045);
        }

        .cc-testimonial-form::before {
            content: "";

            position: absolute;
            top: 0;
            left: 8%;

            width: 84%;
            height: 1px;

            background:
                linear-gradient(90deg,
                    transparent,
                    rgba(168, 85, 247, .55),
                    rgba(124, 58, 237, .2),
                    transparent);
        }


        /* ==========================================================================
                               FORM ROWS
                               ========================================================================== */

        .cc-form-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
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

            color: rgba(255, 255, 255, .72);

            font-size: 12px;
            font-weight: 600;
            letter-spacing: .01em;
        }

        .cc-optional {
            color: rgba(255, 255, 255, .24);

            font-size: 10px;
            font-weight: 400;
        }


        /* ==========================================================================
                               INPUTS
                               ========================================================================== */

        .cc-testimonial-input-wrap {
            position: relative;
        }

        .cc-testimonial-input-wrap input,
        .cc-testimonial-input-wrap textarea {
            display: block;

            width: 100%;

            padding: 16px 17px;

            border: 1px solid rgba(255, 255, 255, .075);
            border-radius: 11px;

            outline: none;

            color: #fff;

            background:
                linear-gradient(145deg,
                    rgba(30, 13, 46, .58),
                    rgba(5, 3, 9, .78));

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


        /* Hover */

        .cc-testimonial-input-wrap input:hover,
        .cc-testimonial-input-wrap textarea:hover {
            border-color: rgba(168, 85, 247, .28);

            background:
                linear-gradient(145deg,
                    rgba(38, 17, 57, .65),
                    rgba(7, 4, 12, .82));
        }


        /* Focus */

        .cc-testimonial-input-wrap input:focus,
        .cc-testimonial-input-wrap textarea:focus {
            border-color: rgba(168, 85, 247, .58);

            background:
                linear-gradient(145deg,
                    rgba(32, 14, 50, .75),
                    rgba(5, 3, 9, .9));

            box-shadow:
                0 0 0 3px rgba(124, 58, 237, .08),
                0 15px 45px rgba(0, 0, 0, .25),
                0 0 35px rgba(124, 58, 237, .08);
        }

        .cc-testimonial-input-wrap input::placeholder,
        .cc-testimonial-input-wrap textarea::placeholder {
            color: rgba(255, 255, 255, .2);
        }


        /* ==========================================================================
                               PURPLE INPUT LINE
                               ========================================================================== */

        .cc-input-line {
            position: absolute;

            left: 14px;
            right: 14px;
            bottom: 0;

            height: 1px;

            background:
                linear-gradient(90deg,
                    transparent,
                    #8b5cf6,
                    transparent);

            transform: scaleX(0);
            transform-origin: center;

            transition: transform .3s ease;

            pointer-events: none;
        }

        .cc-testimonial-input-wrap:focus-within .cc-input-line {
            transform: scaleX(1);
        }


        /* ==========================================================================
                               VALIDATION
                               ========================================================================== */

        .cc-input-error {
            border-color: rgba(239, 68, 68, .55) !important;

            box-shadow:
                0 0 0 3px rgba(239, 68, 68, .035) !important;
        }

        .cc-input-valid {
            border-color: rgba(168, 85, 247, .25) !important;
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


        /* ==========================================================================
                               RATING
                               ========================================================================== */

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

            border: 1px solid rgba(255, 255, 255, .07);
            border-radius: 10px;

            outline: none;

            color: rgba(255, 255, 255, .17);

            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .035),
                    rgba(255, 255, 255, .01));

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
            border-color: rgba(168, 85, 247, .4);

            color: #a855f7;

            background:
                linear-gradient(145deg,
                    rgba(124, 58, 237, .13),
                    rgba(124, 58, 237, .05));

            transform: translateY(-2px);

            box-shadow:
                0 8px 25px rgba(124, 58, 237, .1);
        }

        .cc-rating__star:hover span {
            transform: translateY(-1px) scale(1.08);
        }

        .cc-rating__star.is-active {
            border-color: rgba(168, 85, 247, .38);

            color: #a855f7;

            background:
                linear-gradient(145deg,
                    rgba(124, 58, 237, .16),
                    rgba(124, 58, 237, .07));

            box-shadow:
                0 0 18px rgba(124, 58, 237, .1),
                inset 0 0 12px rgba(124, 58, 237, .025);
        }

        .cc-rating__star.is-active span {
            color: #a855f7;

            filter:
                drop-shadow(0 0 5px rgba(168, 85, 247, .3));
        }

        .cc-rating__star:focus-visible {
            border-color: rgba(168, 85, 247, .65);

            box-shadow:
                0 0 0 3px rgba(124, 58, 237, .1),
                0 0 25px rgba(124, 58, 237, .1);
        }

        .cc-rating__star:active {
            transform: translateY(0) scale(.94);
        }

        .cc-rating__label {
            margin-left: 9px;

            min-width: 30px;

            color: rgba(255, 255, 255, .38);

            font-size: 11px;
            font-weight: 600;

            transition: color .2s ease;
        }

        .cc-rating:has(.cc-rating__star.is-active) .cc-rating__label {
            color: rgba(168, 85, 247, .75);
        }


        /* ==========================================================================
                               SUBMIT BUTTON
                               ========================================================================== */

        .cc-testimonial-form__submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 12px;

            width: 100%;
            min-height: 58px;

            padding: 0 24px;

            border: 0;
            border-radius: 11px;

            color: #fff;

            background:
                linear-gradient(135deg,
                    #7c3aed 0%,
                    #6d28d9 100%);

            font-family: inherit;
            font-size: 14px;
            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 14px 40px rgba(124, 58, 237, .18);

            transition:
                transform .25s ease,
                box-shadow .25s ease,
                background .25s ease;
        }

        .cc-testimonial-form__submit:hover {
            transform: translateY(-2px);

            background:
                linear-gradient(135deg,
                    #8b5cf6 0%,
                    #7c3aed 100%);

            box-shadow:
                0 18px 50px rgba(124, 58, 237, .28);
        }

        .cc-testimonial-form__submit:active {
            transform: translateY(0);
        }

        .cc-testimonial-form__submit:disabled {
            cursor: wait;
            opacity: .6;
            transform: none;
        }


        /* ==========================================================================
                               SPINNER
                               ========================================================================== */

        .cc-submit-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .cc-submit-spinner i {
            display: block;

            width: 15px;
            height: 15px;

            border: 2px solid rgba(255, 255, 255, .25);
            border-top-color: #fff;

            border-radius: 50%;

            animation:
                cc-testimonial-spin .7s linear infinite;
        }

        @keyframes cc-testimonial-spin {
            to {
                transform: rotate(360deg);
            }
        }


        /* ==========================================================================
                               STATUS MESSAGES
                               ========================================================================== */

        .cc-form-status {
            display: flex;
            align-items: center;
            gap: 14px;

            margin-bottom: 24px;
            padding: 15px 16px;

            border-radius: 13px;

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

            color: rgba(255, 255, 255, .4);

            font-size: 11px;
            line-height: 1.5;
        }


        /* Success */

        .cc-form-status--success {
            border: 1px solid rgba(168, 85, 247, .18);

            background:
                linear-gradient(145deg,
                    rgba(124, 58, 237, .08),
                    rgba(124, 58, 237, .015));
        }

        .cc-form-status--success .cc-form-status__icon {
            color: #a855f7;

            background: rgba(124, 58, 237, .1);
        }


        /* Error */

        .cc-form-status--error {
            border: 1px solid rgba(239, 68, 68, .15);

            background:
                linear-gradient(145deg,
                    rgba(239, 68, 68, .055),
                    rgba(239, 68, 68, .012));
        }

        .cc-form-status--error .cc-form-status__icon {
            color: #f87171;

            background: rgba(239, 68, 68, .08);
        }


        /* ==========================================================================
                               PRIVACY
                               ========================================================================== */

        .cc-testimonial-form__privacy {
            margin: 13px 0 0;

            text-align: center;

            color: rgba(255, 255, 255, .2);

            font-size: 10px;
            line-height: 1.5;
        }

        /* ==========================================================================
   RECAPTCHA DISCLOSURE
   ========================================================================== */

/*
 * Google permits the reCAPTCHA badge to be hidden when the
 * required reCAPTCHA disclosure is displayed elsewhere on the page.
 */
.grecaptcha-badge {
    visibility: hidden !important;
}

.cc-testimonial-form__recaptcha {
    margin: 8px 0 0;
    color: rgba(255, 255, 255, .28);
    font-size: 9px;
    line-height: 1.5;
    text-align: center;
}

.cc-testimonial-form__recaptcha a {
    color: rgba(255, 255, 255, .42);
    text-decoration: underline;
    text-underline-offset: 2px;
}

.cc-testimonial-form__recaptcha a:hover {
    color: rgba(255, 255, 255, .7);
}


        /* ==========================================================================
                               HONEYPOT
                               ========================================================================== */

        .cc-testimonial-honeypot {
            position: absolute !important;

            width: 1px !important;
            height: 1px !important;

            overflow: hidden !important;

            opacity: 0 !important;

            pointer-events: none !important;
        }


        /* ==========================================================================
                               RESPONSIVE
                               ========================================================================== */

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

            .cc-testimonial-submit__copy h2 {
                white-space: normal;
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

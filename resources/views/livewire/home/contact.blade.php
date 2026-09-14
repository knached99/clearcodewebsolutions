<section id="contact" class="cc-contact">

    <div class="cc-contact__orb"></div>

    <div class="cc-container">

        <div class="cc-contact__inner">

            {{-- Contact Copy --}}
            <div class="cc-contact__copy">

                <span class="cc-eyebrow">
                    06 / LET'S TALK
                </span>

                <h2>
                    <span style="white-space: nowrap;">Have an idea?</span>
                    <span>Let's build it.</span>
                </h2>

                <p>
                    Tell us what you're working on and we'll
                    figure out the best way to bring it to life.
                </p>

                <a href="mailto:hello@clearcodewebsolutions.com" class="cc-contact__email">
                    hello@clearcodewebsolutions.com
                    <span>↗</span>
                </a>

            </div>


            {{-- Contact Form --}}
            <form wire:submit="prepareSubmit" class="cc-contact-form">

                {{-- Success Message --}}
                @if ($submitted)
                    <div class="cc-form-status cc-form-status--success" wire:key="success-message">
                        <div class="cc-form-status__icon">
                            ✓
                        </div>

                        <div>
                            <strong>Message sent.</strong>

                            <span class="text-gray-100">
                                Thanks for reaching out. We'll be in touch soon.
                            </span>
                        </div>
                    </div>
                @endif


                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="cc-form-status cc-form-status--error" wire:key="error-message">
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

                <div class="cc-form-row">

                    {{-- Name --}}
                    <div class="cc-form-field">
                        <label for="contact-name">Name</label>

                        <input id="contact-name" type="text" wire:model.live.debounce.500ms="name"
                            placeholder="Your name" @class([
                                'cc-input-error' => $errors->has('name'),
                                'cc-input-valid' => !$errors->has('name') && filled($name),
                            ])>

                        @error('name')
                            <span class="cc-form-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="cc-form-field">
                        <label for="contact-email">Email</label>

                        <input id="contact-email" type="email" wire:model.live.debounce.500ms="email"
                            placeholder="you@company.com" @class([
                                'cc-input-error' => $errors->has('email'),
                                'cc-input-valid' => !$errors->has('email') && filled($email),
                            ])>

                        @error('email')
                            <span class="cc-form-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                </div>

                <div class="cc-form-field">


                    <div class="cc-form-field">
                        <label for="contact-subject">
                            What can we help you with?
                        </label>

                        <div class="cc-select" x-data="{
                            open: false,
                            selected: @entangle('subject').live,
                            options: [
                                'Website Design',
                                'Website Development',
                                'Mobile App Development',
                                'E-Commerce',
                                'Website Redesign',
                                'Maintenance & Support',
                                'Other'
                            ],
                            select(option) {
                                this.selected = option;
                                this.open = false;
                            }
                        }" x-on:click.outside="open = false"
                            x-on:keydown.escape.window="open = false">
                            {{-- Hidden native select keeps the form accessible / compatible --}}
                            <select id="contact-subject" wire:model.live="subject" class="cc-select__native"
                                tabindex="-1" aria-hidden="true">
                                <option value="">Select a subject</option>
                                <option value="Website Design">Website Design</option>
                                <option value="Website Development">Website Development</option>
                                <option value="Mobile App Development">Mobile App Development</option>
                                <option value="E-Commerce">E-Commerce</option>
                                <option value="Website Redesign">Website Redesign</option>
                                <option value="Maintenance & Support">Maintenance & Support</option>
                                <option value="Other">Other</option>
                            </select>

                            {{-- Custom trigger --}}
                            <button type="button" class="cc-select__trigger"
                                :class="{
                                    'is-open': open,
                                    'cc-input-valid': selected,
                                    'cc-input-error': @js($errors->has('subject'))
                                }"
                                @click="open = !open" @keydown.arrow-down.prevent="open = true"
                                @keydown.arrow-up.prevent="open = true" :aria-expanded="open" aria-haspopup="listbox"
                                aria-controls="contact-subject-options">
                                <span class="cc-select__value" :class="{ 'is-placeholder': !selected }"
                                    x-text="selected || 'Select a subject'"></span>

                                <span class="cc-select__arrow" aria-hidden="true">
                                    <svg viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </button>

                            {{-- Animated options --}}
                            <div id="contact-subject-options" class="cc-select__menu" x-show="open"
                                x-transition:enter="cc-select-enter" x-transition:enter-start="cc-select-enter-start"
                                x-transition:enter-end="cc-select-enter-end" x-transition:leave="cc-select-leave"
                                x-transition:leave-start="cc-select-leave-start"
                                x-transition:leave-end="cc-select-leave-end" role="listbox"
                                aria-label="Select a subject" x-cloak>
                                <template x-for="(option, index) in options" :key="option">
                                    <button type="button" class="cc-select__option"
                                        :class="{ 'is-selected': selected === option }" role="option"
                                        :aria-selected="selected === option" @click="select(option)" x-text="option"
                                        :style="`--option-index: ${index}`"></button>
                                </template>
                            </div>
                        </div>

                        @error('subject')
                            <span class="cc-form-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>


                {{-- Message --}}
                <div class="cc-form-field">
                    <label for="contact-message">
                        What can we build together?
                    </label>

                    <textarea id="contact-message" wire:model.live.debounce.500ms="message" rows="5"
                        placeholder="Tell us a little about your project..." @class([
                            'cc-input-error' => $errors->has('message'),
                            'cc-input-valid' => !$errors->has('message') && filled($message),
                        ])></textarea>

                    @error('message')
                        <span class="cc-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit" class="cc-contact-form__submit" wire:loading.attr="disabled"
                    wire:target="prepareSubmit,submit">
                    {{-- Normal state --}}
                    <span wire:loading.remove wire:target="prepareSubmit,submit">
                        Start a conversation
                    </span>

                    <span wire:loading.remove wire:target="prepareSubmit,submit">
                        ↗
                    </span>

                    {{-- Loading state --}}
                    <span wire:loading wire:target="prepareSubmit,submit" class="cc-submit-spinner"
                        aria-hidden="true">
                        <i></i>
                    </span>

                    <span wire:loading wire:target="prepareSubmit,submit">
                        Sending...
                    </span>
                </button>

                </button>

                <p class="cc-contact-form__recaptcha">
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

@push('styles')
    <style>
        /* ==========================================================================
                               RECAPTCHA
                               ========================================================================== */

        /*
                             * reCAPTCHA v3 badge is hidden because the required disclosure
                             * is displayed directly beneath the contact form submit button.
                             */
        .grecaptcha-badge {
            visibility: hidden !important;
        }

        .cc-contact-form__recaptcha {
            margin: 10px 0 0;
            color: #fff;
            font-size: 12px;
            line-height: 1.5;
            text-align: center;
        }

        .cc-contact-form__recaptcha a {
            color: rgb(194, 119, 255);
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 2px;
        }

        .cc-contact-form__recaptcha a:hover {
            color: rgba(137, 28, 253, 0.7);
        }
    </style>
@endpush

@once
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.google_recaptcha.site_key') }}" async
        defer></script>
@endonce

@script
    <script>
        $wire.on(
            'execute-contact-recaptcha',
            async () => {

                try {

                    if (
                        typeof grecaptcha === 'undefined'
                    ) {
                        $wire.addError(
                            'form',
                            'Security verification is still loading. Please try again.'
                        );

                        return;
                    }

                    await new Promise((resolve) => {
                        grecaptcha.ready(resolve);
                    });

                    const token =
                        await grecaptcha.execute(
                            '{{ config('services.google_recaptcha.site_key') }}', {
                                action: 'contact_submit'
                            }
                        );

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

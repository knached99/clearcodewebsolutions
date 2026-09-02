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
                    Have an idea?
                    <span>Let's build it.</span>
                </h2>

                <p>
                    Tell us what you're working on and we'll
                    figure out the best way to bring it to life.
                </p>

                <a
                    href="mailto:hello@clearcodewebsolutions.com"
                    class="cc-contact__email"
                >
                    hello@clearcodewebsolutions.com
                    <span>↗</span>
                </a>

            </div>


            {{-- Contact Form --}}
            <form
                wire:submit="submit"
                class="cc-contact-form"
            >

                {{-- Success Message --}}
                @if($submitted)

                    <div
                        class="cc-form-status cc-form-status--success"
                        wire:key="success-message"
                    >
                        <div class="cc-form-status__icon">
                            ✓
                        </div>

                        <div>
                            <strong>Message sent.</strong>

                            <span>
                                Thanks for reaching out. We'll be in touch soon.
                            </span>
                        </div>
                    </div>

                @endif


                {{-- Validation Errors --}}
                @if($errors->any())

                    <div
                        class="cc-form-status cc-form-status--error"
                        wire:key="error-message"
                    >
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


                <div class="cc-form-row">

                    {{-- Name --}}
                   <div class="cc-form-field">
                    <label for="contact-name">Name</label>

                    <input
                        id="contact-name"
                        type="text"
                        wire:model.live.debounce.500ms="name"
                        placeholder="Your name"
                        @class([
                            'cc-input-error' => $errors->has('name'),
                            'cc-input-valid' => !$errors->has('name') && filled($name),
                        ])
                    >

                    @error('name')
                        <span class="cc-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                    {{-- Email --}}
                    <div class="cc-form-field">
                    <label for="contact-email">Email</label>

                    <input
                        id="contact-email"
                        type="email"
                        wire:model.live.debounce.500ms="email"
                        placeholder="you@company.com"
                        @class([
                            'cc-input-error' => $errors->has('email'),
                            'cc-input-valid' => !$errors->has('email') && filled($email),
                        ])
                    >

                    @error('email')
                        <span class="cc-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                </div>

                <div class="cc-form-field">
    <label for="contact-subject">
        What can we help you with?
    </label>

    <div class="cc-select-wrap">
        <select
            id="contact-subject"
            wire:model.live="subject"
            class="@error('subject') cc-input-error @elseif($subject) cc-input-valid @endif"
        >
            <option value="">
                Select a subject
            </option>

            <option value="Website Design">
                Website Design
            </option>

            <option value="Website Development">
                Website Development
            </option>

            <option value="Mobile App Development">
                Mobile App Development
            </option>

            <option value="E-Commerce">
                E-Commerce
            </option>

            <option value="Website Redesign">
                Website Redesign
            </option>

            <option value="Maintenance & Support">
                Maintenance & Support
            </option>

            <option value="Other">
                Other
            </option>
        </select>

        <span class="cc-select-arrow">
            ↓
        </span>
    </div>

    @error('subject')
        <span class="cc-form-error">
            {{ $message }}
        </span>
    @enderror
</div>


                {{-- Message --}}
                        <div class="cc-form-field">
                <label for="contact-message">
                    What can we build together?
                </label>

                <textarea
                    id="contact-message"
                    wire:model.live.debounce.500ms="message"
                    rows="5"
                    placeholder="Tell us a little about your project..."
                    @class([
                        'cc-input-error' => $errors->has('message'),
                        'cc-input-valid' => !$errors->has('message') && filled($message),
                    ])
                ></textarea>

                @error('message')
                    <span class="cc-form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>

                {{-- Submit --}}
                 <button
                type="submit"
                class="cc-contact-form__submit"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                {{-- Normal state --}}
                <span wire:loading.remove wire:target="submit">
                    Start a conversation
                </span>

                <span wire:loading.remove wire:target="submit">
                    ↗
                </span>

                {{-- Loading state --}}
                <span
                    wire:loading
                    wire:target="submit"
                    class="cc-submit-spinner"
                    aria-hidden="true"
                >
                    <i></i>
                </span>

                <span
                    wire:loading
                    wire:target="submit"
                >
                    Sending...
                </span>
            </button>

            </form>

        </div>

    </div>

</section>
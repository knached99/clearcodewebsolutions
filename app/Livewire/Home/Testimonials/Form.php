<?php

namespace App\Livewire\Home\Testimonials;

use App\Services\TestimonialService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Form extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $role = '';

    public string $message = '';

    public int $rating = 0;

    /**
     * Honeypot field.
     *
     * This should remain empty for real visitors.
     */
    public string $website = '';

    /**
     * reCAPTCHA v3 token.
     */
    public string $recaptchaToken = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'company' => [
                'nullable',
                'string',
                'max:150',
            ],

            'role' => [
                'nullable',
                'string',
                'max:150',
            ],

            'message' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],

            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'website' => [
                'nullable',
                'string',
                'max:0',
            ],

            'recaptchaToken' => [
                'required',
                'string',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',

            'email.email' => 'Please enter a valid email address.',

            'message.required' => 'Please share your experience with us.',

            'message.min' =>
                'Your testimonial should be at least 20 characters.',

            'message.max' =>
                'Your testimonial is too long.',

            'rating.required' =>
                'Please select a rating.',

            'rating.min' =>
                'Please select a rating.',

            'rating.max' =>
                'Please select a rating.',

            'recaptchaToken.required' =>
                'Please wait a moment and try submitting again.',
        ];
    }

    /**
     * Set testimonial rating.
     */
    public function setRating(int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            return;
        }

        $this->rating = $rating;

        $this->resetValidation('rating');
    }

    /**
     * Called when the user clicks Submit.
     *
     * We generate the reCAPTCHA token immediately before
     * actually submitting the testimonial.
     */
    public function prepareSubmit(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        $this->dispatch('execute-testimonial-recaptcha');
    }

    /**
     * Final submission after the browser has obtained
     * the reCAPTCHA v3 token.
     */
    public function submit(
        string $recaptchaToken,
        TestimonialService $service
    ): void {
        /*
         * Store the freshly generated token.
         */
        $this->recaptchaToken = $recaptchaToken;

        /*
         * ---------------------------------------------
         * HONEYPOT
         * ---------------------------------------------
         *
         * Real users should never populate this field.
         *
         * We silently accept the submission from bots so
         * they don't receive a useful indication that they
         * triggered bot detection.
         */
        if (filled($this->website)) {
            $this->resetForm();

            $this->submitted = true;

            return;
        }

        /*
         * ---------------------------------------------
         * RATE LIMIT
         * ---------------------------------------------
         *
         * 3 submissions per hour per IP.
         */
        $key = 'testimonial-submission:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError(
                'form',
                'Too many submissions. Please try again later.'
            );

            return;
        }

        /*
         * ---------------------------------------------
         * FORM VALIDATION
         * ---------------------------------------------
         */
        $validated = $this->validate();

        /*
         * ---------------------------------------------
         * RECAPTCHA VERIFICATION
         * ---------------------------------------------
         */
        $recaptcha = $this->verifyRecaptcha(
            $recaptchaToken
        );

        if (!$recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message']
            );

            return;
        }

        /*
         * Only consume the rate-limit attempt after
         * the request successfully passes reCAPTCHA.
         */
        RateLimiter::hit($key, 3600);

        /*
         * ---------------------------------------------
         * REMOVE SECURITY-ONLY FIELDS
         * ---------------------------------------------
         */
        unset(
            $validated['website'],
            $validated['recaptchaToken']
        );

        /*
         * ---------------------------------------------
         * CREATE TESTIMONIAL
         * ---------------------------------------------
         */
        $service->create($validated);

        /*
         * ---------------------------------------------
         * RESET
         * ---------------------------------------------
         */
        $this->resetForm();

        $this->submitted = true;

        $this->dispatch(
            'testimonial-submitted'
        );
    }

    /**
     * Verify a reCAPTCHA v3 token with Google.
     *
     * Google returns:
     *
     * success
     * score
     * action
     *
     * We verify all three.
     */
    protected function verifyRecaptcha(
        string $token
    ): array {
        $secret = config(
            'services.google_recaptcha.secret_key'
        );

        if (blank($secret)) {
            report(
                new \RuntimeException(
                    'Google reCAPTCHA secret key is not configured.'
                )
            );

            return [
                'success' => false,
                'message' =>
                    'Security verification is currently unavailable. Please try again later.',
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,

                        'response' => $token,

                        /*
                         * Google can use this to evaluate
                         * the originating client IP.
                         */
                        'remoteip' => request()->ip(),
                    ]
                );
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' =>
                    'We could not complete the security check. Please try again.',
            ];
        }

        /*
         * Google did not return a successful HTTP response.
         */
        if (!$response->successful()) {
            report(
                new \RuntimeException(
                    'Google reCAPTCHA request failed: ' .
                    $response->body()
                )
            );

            return [
                'success' => false,
                'message' =>
                    'We could not complete the security check. Please try again.',
            ];
        }

        $data = $response->json();

        /*
         * ---------------------------------------------
         * TOKEN VALIDITY
         * ---------------------------------------------
         */
        if (!data_get($data, 'success', false)) {
            report(
                new \RuntimeException(
                    'reCAPTCHA verification failed: ' .
                    json_encode(
                        data_get($data, 'error-codes', [])
                    )
                )
            );

            return [
                'success' => false,
                'message' =>
                    'The security verification failed. Please try again.',
            ];
        }

        /*
         * ---------------------------------------------
         * ACTION VALIDATION
         * ---------------------------------------------
         *
         * The browser generates the token using:
         *
         * testimonial_submit
         *
         * We make sure Google reports the exact same action.
         */
        $action = data_get(
            $data,
            'action'
        );

        if ($action !== 'testimonial_submit') {
            report(
                new \RuntimeException(
                    'Unexpected reCAPTCHA action: ' .
                    (string) $action
                )
            );

            return [
                'success' => false,
                'message' =>
                    'The security verification failed. Please try again.',
            ];
        }

        /*
         * ---------------------------------------------
         * SCORE
         * ---------------------------------------------
         *
         * reCAPTCHA v3 returns a score from 0.0 to 1.0.
         *
         * Start with 0.5. We can tune this after testing
         * your real traffic.
         */
        $score = (float) data_get(
            $data,
            'score',
            0
        );

        $minimumScore = 0.5;

        if ($score < $minimumScore) {
            return [
                'success' => false,
                'message' =>
                    'We could not verify that this submission came from a real visitor. Please try again.',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
        ];
    }

    /**
     * Reset the form.
     */
    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'company',
            'role',
            'message',
            'website',
            'recaptchaToken',
        ]);

        $this->rating = 0;
    }

    public function render()
    {
        return view(
            'livewire.home.testimonials.form'
        );
    }
}
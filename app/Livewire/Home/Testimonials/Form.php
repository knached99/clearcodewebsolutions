<?php

namespace App\Livewire\Home\Testimonials;

use App\Services\TestimonialService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class Form extends Component
{
    use UsesSpamProtection;

    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $role = '';

    public string $message = '';

    public int $rating = 0;

    /**
     * Spatie honeypot data.
     */
    public HoneypotData $extraFields;

    /**
     * Fresh reCAPTCHA v3 token.
     */
    public string $recaptchaToken = '';

    public bool $submitted = false;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:120',
                'regex:/\A[\pL\pM]+(?:[ .\'-][\pL\pM]+)*\z/u',
            ],

            'email' => [
                'bail',
                'nullable',
                'string',
                'lowercase',
                'max:254',
                'email:rfc,dns,spoof',
            ],

            'company' => [
                'bail',
                'nullable',
                'string',
                'max:150',
            ],

            'role' => [
                'bail',
                'nullable',
                'string',
                'max:150',
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:2000',
            ],

            'rating' => [
                'bail',
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5]),
            ],

            'recaptchaToken' => [
                'bail',
                'required',
                'string',
                'max:4096',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',

            'name.regex' => 'Please enter a valid name.',

            'email.email' => 'Please enter a valid email address.',

            'message.required' => 'Please share your experience with us.',

            'message.min' => 'Your testimonial should be at least 20 characters.',

            'message.max' => 'Your testimonial is too long.',

            'rating.required' => 'Please select a rating.',

            'rating.in' => 'Please select a rating from 1 to 5.',

            'recaptchaToken.required' => 'Please wait a moment and try submitting again.',
        ];
    }

    public function updated($property): void
    {
        $rules = $this->typingRules($property);

        if ($rules === []) {
            return;
        }

        $this->validateOnly($property, [
            $property => $rules,
        ]);
    }

    protected function typingRules(string $property): array
    {
        return match ($property) {
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:120',
                'regex:/\A[\pL\pM]+(?:[ .\'-][\pL\pM]+)*\z/u',
            ],

            'email' => [
                'bail',
                'nullable',
                'string',
                'lowercase',
                'max:254',
                'email:rfc',
            ],

            'company',
            'role' => [
                'bail',
                'nullable',
                'string',
                'max:150',
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:2000',
            ],

            'rating' => [
                'bail',
                'required',
                'integer',
                Rule::in([1, 2, 3, 4, 5]),
            ],

            'recaptchaToken' => [],

            default => [],
        };
    }

    /**
     * Set testimonial rating.
     */
    public function setRating(int $rating): void
    {
        if (! in_array($rating, [1, 2, 3, 4, 5], true)) {
            return;
        }

        $this->rating = $rating;

        $this->resetValidation('rating');
    }

    /**
     * Called when the user clicks Submit.
     */
    public function prepareSubmit(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        $this->validate([
            'name' => $this->rules()['name'],
            'email' => $this->rules()['email'],
            'company' => $this->rules()['company'],
            'role' => $this->rules()['role'],
            'message' => $this->rules()['message'],
            'rating' => $this->rules()['rating'],
        ]);

        $this->dispatch(
            'execute-testimonial-recaptcha',
        );
    }

    /**
     * Final submission after reCAPTCHA has been generated.
     */
    public function submit(
        string $recaptchaToken,
        TestimonialService $service,
    ): void {
        $this->submitted = false;

        $this->recaptchaToken = $recaptchaToken;

        /*
         * Spatie honeypot + minimum-time protection.
         */
        $this->protectAgainstSpam();

        /*
         * Rate-limit before making the external reCAPTCHA request.
         */
        $key = $this->rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError(
                'form',
                'Too many submissions. Please try again later.',
            );

            return;
        }

        /*
         * Validate the complete form.
         */
        $this->validate();

        /*
         * Verify reCAPTCHA server-side.
         */
        $recaptcha = $this->verifyRecaptcha(
            token: $recaptchaToken,
            expectedAction: 'testimonial_submit',
        );

        if (! $recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message'],
            );

            return;
        }

        /*
         * Consume the rate-limit attempt only after the
         * security checks succeed.
         */
        RateLimiter::hit($key, 3600);

        /*
         * Explicitly construct the business data.
         *
         * Do NOT pass all Livewire properties to the service.
         */
        $testimonialData = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'company' => $this->company ?: null,
            'role' => $this->role ?: null,
            'message' => $this->message,
            'rating' => $this->rating,
        ];

        $service->create($testimonialData);

        $this->resetForm();

        $this->submitted = true;

        $this->dispatch(
            'testimonial-submitted',
        );
    }

    protected function rateLimitKey(): string
    {
        $ip = request()->ip() ?? 'unknown';

        return 'testimonial-submission:' . hash(
            'sha256',
            $ip,
        );
    }

    /**
     * Verify a reCAPTCHA v3 token with Google.
     */
    protected function verifyRecaptcha(
        string $token,
        string $expectedAction,
    ): array {
        if (
            $token === ''
            || strlen($token) > 4096
        ) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        $secret = config(
            'services.google_recaptcha.secret_key',
        );

        if (blank($secret)) {
            report(
                new \RuntimeException(
                    'Google reCAPTCHA secret key is not configured.',
                ),
            );

            return [
                'success' => false,
                'message' => 'Security verification is currently unavailable. Please try again later.',
            ];
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => request()->ip(),
                    ],
                );
        } catch (\Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'We could not complete the security check. Please try again.',
            ];
        }

        if (! $response->successful()) {
            report(
                new \RuntimeException(
                    'Google reCAPTCHA verification request failed with HTTP status ' .
                    $response->status(),
                ),
            );

            return [
                'success' => false,
                'message' => 'We could not complete the security check. Please try again.',
            ];
        }

        $data = $response->json();

        if (! is_array($data)) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        if (($data['success'] ?? false) !== true) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        if (($data['action'] ?? null) !== $expectedAction) {
            report(
                new \RuntimeException(
                    'Unexpected reCAPTCHA action.',
                ),
            );

            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        $score = $data['score'] ?? null;

        if (! is_numeric($score)) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        $score = (float) $score;

        if ($score < 0.5 || $score > 1.0) {
            return [
                'success' => false,
                'message' => 'We could not verify that this request came from a real visitor. Please try again.',
            ];
        }

        $expectedHostname = config(
            'services.google_recaptcha.hostname',
        );

        if (
            filled($expectedHostname)
            && ($data['hostname'] ?? null) !== $expectedHostname
        ) {
            report(
                new \RuntimeException(
                    'Unexpected reCAPTCHA hostname.',
                ),
            );

            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'company',
            'role',
            'message',
            'recaptchaToken',
        ]);

        $this->rating = 0;

        /*
         * Fresh honeypot payload.
         */
        $this->extraFields = new \Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData();
    }

   public function addError($name, $message)
    {
        parent::addError($name, $message);
    }
        public function render()
    {
        return view(
            'livewire.home.testimonials.form',
        );
    }
}
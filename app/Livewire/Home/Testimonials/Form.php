<?php

namespace App\Livewire\Home\Testimonials;

use App\Services\RecaptchaService;
use App\Services\TestimonialService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class Form extends Component
{
    use UsesSpamProtection;

    private const RATE_LIMIT_MAX_ATTEMPTS = 3;
    private const RATE_LIMIT_DECAY_SECONDS = 3600;
    private const RECAPTCHA_MAX_LENGTH = 4096;

    public string $name = '';
    public string $email = '';
    public string $company = '';
    public string $role = '';
    public string $message = '';
    public int $rating = 0;

    public HoneypotData $extraFields;

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
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',
            'name.regex' => 'Please enter a valid name.',
            'name.min' => 'Your name must be at least 2 characters.',
            'name.max' => 'Your name is too long.',

            'email.email' => 'Please enter a valid email address.',

            'company.max' => 'Your company name is too long.',
            'role.max' => 'Your role is too long.',

            'message.required' => 'Please share your experience with us.',
            'message.min' => 'Your testimonial should be at least 20 characters.',
            'message.max' => 'Your testimonial is too long.',

            'rating.required' => 'Please select a rating.',
            'rating.in' => 'Please select a rating from 1 to 5.',
        ];
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

            default => [],
        };
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

    public function setRating(int $rating): void
    {
        if (! in_array($rating, [1, 2, 3, 4, 5], true)) {
            return;
        }

        $this->rating = $rating;

        $this->resetValidation('rating');
    }

    /**
     * Browser-side preparation step.
     *
     * This does NOT perform the final submission.
     * The final submit() method independently repeats all
     * security-sensitive checks because Livewire methods
     * can be invoked directly by an attacker.
     */
    public function prepareSubmit(): void
    {
        $this->submitted = false;

        $this->resetValidation([
            'form',
        ]);

        $this->validate([
            'name' => $this->rules()['name'],
            'email' => $this->rules()['email'],
            'company' => $this->rules()['company'],
            'role' => $this->rules()['role'],
            'message' => $this->rules()['message'],
            'rating' => $this->rules()['rating'],
        ]);

        $this->dispatch('execute-testimonial-recaptcha');
    }

    /**
     * Final submission endpoint.
     *
     * Security order:
     *
     * 1. Rate-limit check
     * 2. Rate-limit hit
     * 3. Honeypot
     * 4. Cheap Laravel validation
     * 5. reCAPTCHA verification
     * 6. Business/service operation
     * 7. Database
     */
    public function submit(
        string $recaptchaToken,
        TestimonialService $service,
        RecaptchaService $recaptchaService,
    ): void {
        $this->submitted = false;

        /*
         * ---------------------------------------------------------
         * 1. RATE LIMIT CHECK
         * ---------------------------------------------------------
         */

        $key = $this->rateLimitKey();

        if (
            RateLimiter::tooManyAttempts(
                $key,
                self::RATE_LIMIT_MAX_ATTEMPTS,
            )
        ) {
            $seconds = RateLimiter::availableIn($key);

            $minutes = max(
                1,
                (int) ceil($seconds / 60),
            );

            $this->addError(
                'form',
                "Too many submissions. Please try again in {$minutes} minute(s).",
            );

            return;
        }

        /*
         * ---------------------------------------------------------
         * 2. RATE LIMIT HIT
         * ---------------------------------------------------------
         *
         * Consume the attempt BEFORE contacting Google.
         *
         * This prevents an attacker from repeatedly submitting
         * invalid reCAPTCHA tokens and forcing unlimited external
         * reCAPTCHA requests.
         */

        RateLimiter::hit(
            $key,
            self::RATE_LIMIT_DECAY_SECONDS,
        );

        /*
         * ---------------------------------------------------------
         * 3. HONEYPOT
         * ---------------------------------------------------------
         */

        $this->protectAgainstSpam();

        /*
         * ---------------------------------------------------------
         * 4. CHEAP LARAVEL VALIDATION
         * ---------------------------------------------------------
         */

        $this->validate();

        $this->validateRecaptchaToken($recaptchaToken);

        /*
         * ---------------------------------------------------------
         * 5. RECAPTCHA
         * ---------------------------------------------------------
         */

        $recaptcha = $recaptchaService->verifyRecaptcha(
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
         * ---------------------------------------------------------
         * 6. BUSINESS VALIDATION / SERVICE
         * ---------------------------------------------------------
         *
         * Explicitly construct the business payload.
         *
         * Never pass all Livewire properties to the service.
         */

        $testimonialData = [
            'name' => trim($this->name),
            'email' => $this->email !== ''
                ? strtolower(trim($this->email))
                : null,
            'company' => $this->company !== ''
                ? trim($this->company)
                : null,
            'role' => $this->role !== ''
                ? trim($this->role)
                : null,
            'message' => trim($this->message),
            'rating' => $this->rating,
        ];

        /*
         * ---------------------------------------------------------
         * 7. DATABASE
         * ---------------------------------------------------------
         *
         * TestimonialService is responsible for the actual
         * persistence operation.
         */

        $service->create($testimonialData);

        $this->resetForm();

        $this->submitted = true;

        $this->dispatch('testimonial-submitted');
    }

    protected function validateRecaptchaToken(string $token): void
    {
        Validator::make(
            [
                'recaptchaToken' => $token,
            ],
            [
                'recaptchaToken' => [
                    'bail',
                    'required',
                    'string',
                    'max:' . self::RECAPTCHA_MAX_LENGTH,
                ],
            ],
            [
                'recaptchaToken.required' =>
                    'Please wait a moment and try submitting again.',
            ],
        )->validate();
    }

    protected function rateLimitKey(): string
    {
        $ip = request()->ip() ?? 'unknown';

        return 'testimonial-submission:' . hash(
            'sha256',
            $ip,
        );
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'company',
            'role',
            'message',
        ]);

        $this->rating = 0;

        $this->extraFields = new HoneypotData();

        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.home.testimonials.form',
        );
    }
}
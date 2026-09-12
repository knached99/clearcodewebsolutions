<?php

namespace App\Livewire\Home;

use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReceivedNotification;
use App\Services\RecaptchaService;
use App\Services\TicketService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class Contact extends Component
{
    use UsesSpamProtection;

    private const RATE_LIMIT_MAX_ATTEMPTS = 3;
    private const RATE_LIMIT_DECAY_SECONDS = 60;
    private const RECAPTCHA_MAX_LENGTH = 4096;

    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';

    public HoneypotData $extraFields;

    public bool $submitted = false;

    public ?string $ticketNumber = null;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();
    }

    protected function subjects(): array
    {
        return [
            'Website Design',
            'Website Development',
            'Mobile App Development',
            'E-Commerce',
            'Website Redesign',
            'Maintenance & Support',
            'Other',
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:80',
            ],

            'email' => [
                'bail',
                'required',
                'string',
                'lowercase',
                'max:254',
                'email:rfc,dns,spoof',
            ],

            'subject' => [
                'bail',
                'required',
                'string',
                Rule::in($this->subjects()),
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:5000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',
            'name.min' => 'Your name must be at least 2 characters.',
            'name.max' => 'Your name is too long.',

            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',

            'subject.required' => 'Please select a subject.',
            'subject.in' => 'Please select a valid subject.',

            'message.required' => 'Please tell us how we can help.',
            'message.min' => 'Your message should be at least 20 characters.',
            'message.max' => 'Your message is too long.',
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
                'max:80',
            ],

            'email' => [
                'bail',
                'required',
                'string',
                'lowercase',
                'max:254',
                'email:rfc',
            ],

            'subject' => [
                'bail',
                'required',
                'string',
                Rule::in($this->subjects()),
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:5000',
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

    /**
     * Browser-side preparation step.
     *
     * The final submit() method repeats all security-sensitive
     * checks because this method cannot be trusted as an
     * authorization/security boundary.
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
            'subject' => $this->rules()['subject'],
            'message' => $this->rules()['message'],
        ]);

        $this->dispatch('execute-contact-recaptcha');
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
     * 5. reCAPTCHA
     * 6. Business/service operation
     * 7. Database
     */
    public function submit(
        string $recaptchaToken,
        TicketService $ticketService,
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

            throw ValidationException::withMessages([
                'form' => $this->rateLimitMessage($seconds),
            ]);
        }

        /*
         * ---------------------------------------------------------
         * 2. RATE LIMIT HIT
         * ---------------------------------------------------------
         *
         * Consume the attempt BEFORE reCAPTCHA.
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
            expectedAction: 'contact_submit',
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
         */

        $ticketData = [
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'subject' => $this->subject,
            'message' => trim($this->message),
        ];

        /*
         * ---------------------------------------------------------
         * 7. DATABASE
         * ---------------------------------------------------------
         *
         * TicketService performs the actual ticket creation.
         */

        $result = $ticketService->createTicket(
            $ticketData,
        );

        $ticket = $result['ticket'];
        $token = $result['token'];

        /*
         * Notifications happen AFTER the database operation.
         *
         * These notification classes should implement ShouldQueue
         * if you want mail delivery to happen asynchronously.
         */

        Notification::route(
            'mail',
            $ticket->email,
        )->notify(
            new TicketReceivedNotification(
                $ticket,
                $token,
            ),
        );

        $recipients = $ticket->team
            ->members()
            ->select([
                'users.id',
                'users.name',
                'users.email',
            ])
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new NewTicketNotification(
                    $ticket,
                ),
            );
        }

        $this->ticketNumber = $ticket->ticket_number;

        $this->resetForm();

        $this->submitted = true;

        $this->dispatch('contact-submitted');
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

        return 'contact-form:' . hash(
            'sha256',
            $ip,
        );
    }

    protected function rateLimitMessage(int $seconds): string
    {
        $seconds = max(1, $seconds);

        if ($seconds < 60) {
            return "Too many submissions. Please try again in {$seconds} seconds.";
        }

        $minutes = max(
            1,
            (int) ceil($seconds / 60),
        );

        return "Too many submissions. Please try again in {$minutes} minute(s).";
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'subject',
            'message',
        ]);

        $this->ticketNumber = null;

        $this->extraFields = new HoneypotData();

        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.home.contact',
        );
    }
}
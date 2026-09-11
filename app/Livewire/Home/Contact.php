<?php

namespace App\Livewire\Home;

use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReceivedNotification;
use App\Services\TicketService;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class Contact extends Component
{
    use UsesSpamProtection;

    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /**
     * Spatie honeypot data.
     *
     * This replaces the custom $website honeypot field.
     */
    public HoneypotData $extraFields;

    /**
     * Fresh reCAPTCHA v3 token generated immediately before submission.
     */
    public string $recaptchaToken = '';

    public bool $submitted = false;

    public ?string $ticketNumber = null;

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
                'max:80',
                'regex:/\A[\pL\pM]+(?:[ .\'-][\pL\pM]+)*\z/u',
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
                Rule::in([
                    'Website Design',
                    'Website Development',
                    'Mobile App Development',
                    'E-Commerce',
                    'Website Redesign',
                    'Maintenance & Support',
                    'Other',
                ]),
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:5000',
            ],

            'recaptchaToken' => [
                'bail',
                'required',
                'string',
                'max:4096',
            ],
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
                'max:80',
                'regex:/\A[\pL\pM]+(?:[ .\'-][\pL\pM]+)*\z/u',
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
                Rule::in([
                    'Website Design',
                    'Website Development',
                    'Mobile App Development',
                    'E-Commerce',
                    'Website Redesign',
                    'Maintenance & Support',
                    'Other',
                ]),
            ],

            'message' => [
                'bail',
                'required',
                'string',
                'min:20',
                'max:5000',
            ],

            'recaptchaToken' => [],

            default => [],
        };
    }

    /**
     * Called when the visitor clicks Submit.
     *
     * reCAPTCHA is generated immediately before the final submission.
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
            'subject' => $this->rules()['subject'],
            'message' => $this->rules()['message'],
        ]);

        $this->dispatch('execute-contact-recaptcha');
    }

 
   public function submit(
    string $recaptchaToken,
    TicketService $ticketService,
    RecaptchaService $recaptchaService,
): void {
    $this->submitted = false;

    /*
     * The browser controls this value.
     *
     * Store it only long enough to perform validation.
     */
    $this->recaptchaToken = $recaptchaToken;

    /*
     * Spatie honeypot.
     */
    $this->protectAgainstSpam();

    /*
     * Rate limit BEFORE calling Google or sending mail.
     */
    $key = $this->rateLimitKey();

    if (RateLimiter::tooManyAttempts($key, 3)) {
        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => "Too many attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    /*
     * Validate the complete form.
     */
    $this->validate();

    /*
     * Verify reCAPTCHA server side.
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
     * Only consume the rate-limit attempt after
     * validation and reCAPTCHA succeed.
     */
    RateLimiter::hit($key, 60);

    /*
     * Explicitly construct the service input.
     */
    $ticketData = [
        'name' => $this->name,
        'email' => $this->email,
        'subject' => $this->subject,
        'message' => $this->message,
    ];

    /*
     * Create ticket.
     */
    $result = $ticketService->createTicket($ticketData);

    $ticket = $result['ticket'];
    $token = $result['token'];

    /*
     * Customer notification.
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

    /*
     * Team notification.
     */
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
            new NewTicketNotification($ticket),
        );
    }

    /*
     * Success.
     */
    $this->ticketNumber = $ticket->ticket_number;

    $this->resetForm();

    $this->submitted = true;
}

    /**
     * Build a bounded rate-limit key.
     */
    protected function rateLimitKey(): string
    {
        $ip = request()->ip() ?? 'unknown';

        return 'contact-form:' . hash(
            'sha256',
            $ip,
        );
    }

  
    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'subject',
            'message',
            'recaptchaToken',
        ]);

        /*
         * Generate a new Spatie honeypot payload for the next form.
         */
        $this->extraFields = new \Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData();

        $this->ticketNumber = null;
    }

    public function addError($name, $message)
    {
        parent::addError($name, $message);
    }

    public function render()
    {
        return view('livewire.home.contact');
    }
}
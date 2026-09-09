<?php

namespace App\Livewire\Home;

use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReceivedNotification;
use App\Services\TicketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Contact extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /**
     * Honeypot field.
     *
     * Real visitors should never fill this out.
     */
    public string $website = '';

    /**
     * Fresh reCAPTCHA v3 token generated immediately
     * before submission.
     */
    public string $recaptchaToken = '';

    /**
     * Timestamp used to prevent extremely fast automated
     * submissions.
     */
    public int $formLoadedAt = 0;

    public bool $submitted = false;

    public ?string $ticketNumber = null;

    public function mount(): void
    {
        $this->formLoadedAt = now()->timestamp;
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                'regex:/^[\pL\pM\s\'\-.]+$/u',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'max:254',
                'email:rfc,dns,spoof',
            ],

            'subject' => [
                'required',
                'string',
                'in:Website Design,Website Development,Mobile App Development,E-Commerce,Website Redesign,Maintenance & Support,Other',
            ],

            'message' => [
                'required',
                'string',
                'min:20',
                'max:5000',
            ],

            /*
             * Honeypot must remain empty.
             */
            'website' => [
                'prohibited',
            ],

            /*
             * reCAPTCHA token must be present.
             */
            'recaptchaToken' => [
                'required',
                'string',
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
                'required',
                'string',
                'min:2',
                'max:80',
                'regex:/^[\pL\pM\s\'\-.]+$/u',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'max:254',
                'email:rfc',
            ],

            'subject' => [
                'required',
                'string',
                'in:Website Design,Website Development,Mobile App Development,E-Commerce,Website Redesign,Maintenance & Support,Other',
            ],

            'message' => [
                'required',
                'string',
                'min:20',
                'max:5000',
            ],

            /*
             * Do NOT validate the honeypot while the user is typing.
             */
            default => [],
        };
    }

    /**
     * First step when the visitor clicks Submit.
     *
     * We don't immediately process the form.
     * Instead we ask the browser to generate a fresh
     * reCAPTCHA v3 token.
     */
    public function prepareSubmit(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        $this->dispatch(
            'execute-contact-recaptcha'
        );
    }

    /**
     * Final form submission.
     *
     * The browser passes the freshly generated
     * reCAPTCHA token into this method.
     */
    public function submit(
        string $recaptchaToken,
        TicketService $ticketService
    ): void {
        /*
         * Store the fresh token.
         */
        $this->recaptchaToken = $recaptchaToken;

        $this->submitted = false;

        /*
         * =========================================================
         * HONEYPOT
         * =========================================================
         *
         * Normal visitors never interact with this field.
         *
         * If a bot fills it, silently stop processing.
         */
        if (filled($this->website)) {
            $this->resetForm();

            return;
        }

        /*
         * =========================================================
         * MINIMUM COMPLETION TIME
         * =========================================================
         *
         * Prevent extremely fast automated submissions.
         */
        if (
            $this->formLoadedAt &&
            now()->timestamp - $this->formLoadedAt < 3
        ) {
            throw ValidationException::withMessages([
                'message' =>
                    'Please take a moment to review your message.',
            ]);
        }

        /*
         * =========================================================
         * RATE LIMITING
         * =========================================================
         *
         * Maximum 3 accepted attempts per minute per IP.
         */
        $key = 'contact-form:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' =>
                    "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        /*
         * =========================================================
         * VALIDATE FORM
         * =========================================================
         */
        $validated = $this->validate();

        /*
         * =========================================================
         * VERIFY reCAPTCHA
         * =========================================================
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
         * =========================================================
         * CONSUME RATE-LIMIT ATTEMPT
         * =========================================================
         *
         * We only consume an attempt after the request passes
         * the reCAPTCHA security check.
         */
        RateLimiter::hit($key, 60);

        /*
         * =========================================================
         * REMOVE SECURITY-ONLY FIELDS
         * =========================================================
         *
         * These fields should never be passed to TicketService.
         */
        unset(
            $validated['website'],
            $validated['recaptchaToken']
        );

        /*
         * =========================================================
         * CREATE TICKET
         * =========================================================
         *
         * createTicket() creates the TicketAccessToken
         * and returns the raw access token.
         */
        $result = $ticketService->createTicket(
            $validated
        );

        $ticket = $result['ticket'];

        $token = $result['token'];

        /*
         * =========================================================
         * CUSTOMER EMAIL
         * =========================================================
         */
        Notification::route(
            'mail',
            $ticket->email
        )->notify(
            new TicketReceivedNotification(
                $ticket,
                $token
            )
        );

        /*
         * =========================================================
         * ADMIN EMAIL
         * =========================================================
         */
        Notification::route(
            'mail',
            config('mail.admin_address')
        )->notify(
            new NewTicketNotification(
                $ticket
            )
        );

        /*
         * =========================================================
         * SUCCESS
         * =========================================================
         */
        $this->ticketNumber = $ticket->ticket_number;

        $this->resetForm();

        $this->submitted = true;

        /*
         * Reset the timer for the next possible submission.
         */
        $this->formLoadedAt = now()->timestamp;
    }

    /**
     * Verify the reCAPTCHA v3 token with Google.
     */
    protected function verifyRecaptcha(
        string $token
    ): array {
        $secret = config(
            'services.google_recaptcha.secret_key'
        );

        /*
         * Make sure Laravel has the secret key.
         */
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
                         * Pass the visitor IP to Google.
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
         * Google request failed.
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
         * =========================================================
         * TOKEN VALIDITY
         * =========================================================
         */
        if (!data_get($data, 'success', false)) {
            report(
                new \RuntimeException(
                    'reCAPTCHA verification failed: ' .
                    json_encode(
                        data_get(
                            $data,
                            'error-codes',
                            []
                        )
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
         * =========================================================
         * ACTION CHECK
         * =========================================================
         *
         * The frontend generates:
         *
         * contact_submit
         *
         * Make sure Google returns exactly the same action.
         */
        $action = data_get(
            $data,
            'action'
        );

        if ($action !== 'contact_submit') {
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
         * =========================================================
         * SCORE CHECK
         * =========================================================
         *
         * reCAPTCHA v3 returns a score between 0.0 and 1.0.
         *
         * Start with 0.5. We can adjust it later based on
         * actual traffic.
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
     * Reset the form to its initial state.
     */
    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'subject',
            'message',
            'website',
            'recaptchaToken',
        ]);

        $this->ticketNumber = null;
    }

    public function render()
    {
        return view(
            'livewire.home.contact'
        );
    }
}
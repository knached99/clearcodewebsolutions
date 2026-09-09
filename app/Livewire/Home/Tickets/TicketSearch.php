<?php

namespace App\Livewire\Home\Tickets;

use App\Services\TicketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class TicketSearch extends Component
{
    public string $email = '';

    public string $verificationCode = '';

    /**
     * Honeypot.
     *
     * Real visitors should never fill this field.
     */
    public string $website = '';

    /**
     * Fresh reCAPTCHA v3 token.
     */
    public string $recaptchaToken = '';

    public bool $emailVerificationSent = false;

    public array $verifiedTicketIds = [];

    public bool $showTicketSelection = false;

    /**
     * |-----------------------------------------------------------------------
     * | Rate Limiting
     * |-----------------------------------------------------------------------
     */

    protected function rateLimitKey(
        string $action,
        ?string $value = null
    ): string {
        $ip = request()->ip() ?? 'unknown';

        $value = $value
            ? strtolower(trim($value))
            : '';

        return sprintf(
            'ticket-search:%s:%s:%s',
            $action,
            $ip,
            $value
        );
    }

    protected function ensureRateLimit(
        string $action,
        int $maxAttempts,
        int $decaySeconds,
        ?string $value = null,
    ): void {
        $key = $this->rateLimitKey(
            $action,
            $value
        );

        if (RateLimiter::tooManyAttempts(
            $key,
            $maxAttempts
        )) {
            $seconds = RateLimiter::availableIn($key);

            $minutes = max(
                1,
                (int) ceil($seconds / 60)
            );

            abort(
                429,
                "Too many attempts. Please try again in {$minutes} minute(s)."
            );
        }

        RateLimiter::hit(
            $key,
            $decaySeconds
        );
    }

    /**
     * |-----------------------------------------------------------------------
     * | Prepare reCAPTCHA for sending the verification email
     * |-----------------------------------------------------------------------
     */

    public function prepareSendVerificationCode(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        /*
         * Validate the email before asking Google to generate
         * a token. This avoids unnecessary reCAPTCHA calls.
         */
        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        /*
         * Honeypot.
         */
        if (filled($this->website)) {
            return;
        }

        /*
         * Ask browser to execute reCAPTCHA.
         */
        $this->dispatch(
            'execute-ticket-send-recaptcha'
        );
    }

    /**
     * |-----------------------------------------------------------------------
     * | Send Email Verification Code
     * |-----------------------------------------------------------------------
     */

    public function sendVerificationCode(
        string $recaptchaToken,
        TicketService $ticketService
    ): void {
        $this->recaptchaToken = $recaptchaToken;

        /*
         * Honeypot.
         *
         * Silently stop if a bot filled the hidden field.
         */
        if (filled($this->website)) {
            $this->resetFormState();

            return;
        }

        /*
         * Validate email.
         */
        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'recaptchaToken' => [
                'required',
                'string',
            ],
        ]);

        /*
         * Rate limit:
         *
         * 3 requests per email/IP every 15 minutes.
         */
        $this->ensureRateLimit(
            action: 'send-verification',
            maxAttempts: 3,
            decaySeconds: 900,
            value: $this->email,
        );

        /*
         * Verify reCAPTCHA.
         */
        $recaptcha = $this->verifyRecaptcha(
            token: $recaptchaToken,
            expectedAction: 'ticket_verification_send',
        );

        if (!$recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message']
            );

            return;
        }

        /*
         * Ask TicketService to send the email code.
         */
        $sent = $ticketService->sendGuestTicketVerificationCode(
            email: $this->email,
            ipAddress: request()->ip(),
        );

        if (!$sent) {
            $this->addError(
                'email',
                'Please wait a moment before requesting another verification code.'
            );

            return;
        }

        $this->emailVerificationSent = true;

        $this->resetErrorBag('email');

        $this->verificationCode = '';

        $this->recaptchaToken = '';
    }

    /**
     * |-----------------------------------------------------------------------
     * | Prepare reCAPTCHA for email verification
     * |-----------------------------------------------------------------------
     */

    public function prepareVerifyEmail(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        /*
         * Validate fields before generating the token.
         */
        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'verificationCode' => [
                'required',
                'digits:6',
            ],
        ]);

        /*
         * Honeypot.
         */
        if (filled($this->website)) {
            return;
        }

        /*
         * Ask browser to execute reCAPTCHA.
         */
        $this->dispatch(
            'execute-ticket-verify-recaptcha'
        );
    }

    /**
     * |-----------------------------------------------------------------------
     * | Verify Email Code
     * |-----------------------------------------------------------------------
     */

    public function verifyEmail(
        string $recaptchaToken,
        TicketService $ticketService
    ): void {
        $this->recaptchaToken = $recaptchaToken;

        /*
         * Honeypot.
         */
        if (filled($this->website)) {
            $this->resetFormState();

            return;
        }

        /*
         * Validate.
         */
        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'verificationCode' => [
                'required',
                'digits:6',
            ],

            'recaptchaToken' => [
                'required',
                'string',
            ],
        ]);

        /*
         * Rate limit:
         *
         * 5 verification attempts per email/IP
         * every 15 minutes.
         */
        $this->ensureRateLimit(
            action: 'verify-email',
            maxAttempts: 5,
            decaySeconds: 900,
            value: $this->email,
        );

        /*
         * Verify reCAPTCHA.
         */
        $recaptcha = $this->verifyRecaptcha(
            token: $recaptchaToken,
            expectedAction: 'ticket_verification_verify',
        );

        if (!$recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message']
            );

            return;
        }

        /*
         * Verify the actual email verification code.
         */
        $tickets = $ticketService->verifyGuestTicketVerificationCode(
            email: $this->email,
            code: $this->verificationCode,
            ipAddress: request()->ip(),
        );

        if (!$tickets) {
            $this->addError(
                'verificationCode',
                'That verification code is invalid or has expired.'
            );

            return;
        }

        /*
         * We no longer need the reCAPTCHA token.
         */
        $this->recaptchaToken = '';

        $this->verifiedTicketIds = collect($tickets)
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values()
            ->all();

        /*
         * -------------------------------------------------------------
         * One ticket
         * -------------------------------------------------------------
         *
         * Email verification has authenticated ownership of
         * the email address.
         *
         * Generate a fresh real guest token.
         */
        if (count($this->verifiedTicketIds) === 1) {
            $ticket = $tickets[0];

            $token = $ticketService->rotateGuestTicketToken(
                ticket: $ticket,
            );

            /*
             * Establish guest session.
             */
            $ticketService->grantGuestTicketAccess(
                ticket: $ticket,
                minutes: 60,
            );

            /*
             * Redirect using the REAL 64-character token.
             */
            $this->redirectRoute(
                'tickets.show',
                [
                    'ticketNumber' => $ticket->ticket_number,
                    'token' => $token,
                ]
            );

            return;
        }

        /*
         * -------------------------------------------------------------
         * Multiple tickets
         * -------------------------------------------------------------
         */
        $this->showTicketSelection = true;

        $this->resetErrorBag();
    }

    /**
     * |-----------------------------------------------------------------------
     * | Select Ticket After Email Verification
     * |-----------------------------------------------------------------------
     */

    public function selectTicket(
        int $ticketId,
        TicketService $ticketService
    ): void {
        /*
         * This is only reachable after successful email verification.
         */
        if (!in_array(
            $ticketId,
            $this->verifiedTicketIds,
            true
        )) {
            abort(403);
        }

        $ticket = $ticketService->findVerifiedEmailTicket(
            email: $this->email,
            ticketId: $ticketId,
        );

        if (!$ticket) {
            abort(404);
        }

        /*
         * Generate a fresh real guest token.
         *
         * This token is the credential whose hash is stored
         * in tickets.guest_token_hash.
         */
        $token = $ticketService->rotateGuestTicketToken(
            ticket: $ticket,
        );

        /*
         * Establish guest session access.
         */
        $ticketService->grantGuestTicketAccess(
            ticket: $ticket,
            minutes: 60,
        );

        /*
         * Redirect using the real token.
         */
        $this->redirectRoute(
            'tickets.show',
            [
                'ticketNumber' => $ticket->ticket_number,
                'token' => $token,
            ]
        );
    }

    /**
     * |-----------------------------------------------------------------------
     * | Change Email
     * |-----------------------------------------------------------------------
     */

    public function changeEmail(): void
    {
        $this->emailVerificationSent = false;

        $this->showTicketSelection = false;

        $this->verificationCode = '';

        $this->recaptchaToken = '';

        $this->verifiedTicketIds = [];

        $this->website = '';

        $this->resetErrorBag();
    }

    /**
     * |-----------------------------------------------------------------------
     * | Verify reCAPTCHA v3 Token
     * |-----------------------------------------------------------------------
     */

    protected function verifyRecaptcha(
        string $token,
        string $expectedAction
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
         * Token validity.
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
         * Make sure the token was generated specifically
         * for the operation we're currently performing.
         */
        $action = data_get(
            $data,
            'action'
        );

        if ($action !== $expectedAction) {
            report(
                new \RuntimeException(
                    'Unexpected reCAPTCHA action: ' .
                    (string) $action .
                    '. Expected: ' .
                    $expectedAction
                )
            );

            return [
                'success' => false,
                'message' =>
                    'The security verification failed. Please try again.',
            ];
        }

        /*
         * Score.
         *
         * v3 scores range from 0.0 to 1.0.
         */
        $score = (float) data_get(
            $data,
            'score',
            0
        );

        /*
         * Starting threshold.
         */
        $minimumScore = 0.5;

        if ($score < $minimumScore) {
            return [
                'success' => false,
                'message' =>
                    'We could not verify that this request came from a real visitor. Please try again.',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
        ];
    }

    /**
     * |-----------------------------------------------------------------------
     * | Reset Form State
     * |-----------------------------------------------------------------------
     */

    protected function resetFormState(): void
    {
        $this->email = '';

        $this->verificationCode = '';

        $this->website = '';

        $this->recaptchaToken = '';

        $this->emailVerificationSent = false;

        $this->verifiedTicketIds = [];

        $this->showTicketSelection = false;
    }

    /**
     * |-----------------------------------------------------------------------
     * | Render
     * |-----------------------------------------------------------------------
     */

    public function render()
    {
        return view(
            'livewire.home.tickets.ticket-search'
        )->layout(
            'layouts.home_layout'
        );
    }
}
<?php

namespace App\Livewire\Home\Tickets;

use App\Services\TicketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class TicketSearch extends Component
{
    use UsesSpamProtection;

    public string $email = '';

    public string $verificationCode = '';

    /**
     * Spatie honeypot data.
     */
    public HoneypotData $extraFields;

    /**
     * Fresh reCAPTCHA v3 token.
     */
    public string $recaptchaToken = '';

    public bool $emailVerificationSent = false;

    /**
     * UI state only.
     *
     * Never use this as the source of authorization.
     */
    public array $verifiedTicketIds = [];

    public bool $showTicketSelection = false;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'email' => $this->emailRules(),

            'verificationCode' => $this->verificationRules(),

            'recaptchaToken' => [
                'bail',
                'required',
                'string',
                'max:4096',
            ],
        ];
    }

    protected function emailRules(): array
    {
        return [
            'bail',
            'required',
            'string',
            'lowercase',
            'max:254',
            'email:rfc,dns,spoof',
        ];
    }

    protected function verificationRules(): array
    {
        return [
            'bail',
            'required',
            'string',
            'size:6',
            'regex:/\A[0-9]{6}\z/D',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Rate limiting
    |--------------------------------------------------------------------------
    */

    protected function rateLimitKey(
        string $action,
        ?string $value = null,
    ): string {
        $ip = request()->ip() ?? 'unknown';

        $normalizedValue = $value !== null
            ? strtolower(trim($value))
            : '';

        $ipHash = hash(
            'sha256',
            $ip,
        );

        $valueHash = hash(
            'sha256',
            $normalizedValue,
        );

        return sprintf(
            'ticket-search:%s:%s:%s',
            $action,
            $ipHash,
            $valueHash,
        );
    }

    protected function ensureRateLimit(
        string $action,
        int $maxAttempts,
        int $decaySeconds,
        ?string $value = null,
    ): void {
        $key = $this->rateLimitKey(
            action: $action,
            value: $value,
        );

        if (RateLimiter::tooManyAttempts(
            $key,
            $maxAttempts,
        )) {
            $seconds = RateLimiter::availableIn($key);

            $minutes = max(
                1,
                (int) ceil($seconds / 60),
            );

            abort(
                429,
                "Too many attempts. Please try again in {$minutes} minute(s).",
            );
        }

        RateLimiter::hit(
            $key,
            $decaySeconds,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Send verification code
    |--------------------------------------------------------------------------
    */

    public function prepareSendVerificationCode(): void
    {
        $this->resetValidation([
            'form',
            'email',
            'recaptchaToken',
        ]);

        $this->validate([
            'email' => $this->emailRules(),
        ]);

        /*
         * Spatie honeypot check.
         *
         * This must happen before requesting the reCAPTCHA token.
         */
        $this->protectAgainstSpam();

        $this->dispatch(
            'execute-ticket-send-recaptcha',
        );
    }

    public function sendVerificationCode(
        string $recaptchaToken,
        TicketService $ticketService,
    ): void {
        $this->recaptchaToken = $recaptchaToken;

        /*
         * Honeypot + minimum-time protection.
         */
        $this->protectAgainstSpam();

        /*
         * Validate before doing anything expensive.
         */
        $this->validate([
            'email' => $this->emailRules(),

            'recaptchaToken' => [
                'bail',
                'required',
                'string',
                'max:4096',
            ],
        ]);

        /*
         * Rate-limit by IP + email.
         *
         * This prevents repeatedly targeting one email address.
         */
        $this->ensureRateLimit(
            action: 'send-verification',
            maxAttempts: 3,
            decaySeconds: 900,
            value: $this->email,
        );

        /*
         * Independent IP-only limit.
         *
         * This prevents bypassing the first limit by rotating emails.
         */
        $this->ensureRateLimit(
            action: 'send-verification-ip',
            maxAttempts: 10,
            decaySeconds: 900,
        );

        /*
         * Verify reCAPTCHA.
         */
        $recaptcha = $this->verifyRecaptcha(
            token: $recaptchaToken,
            expectedAction: 'ticket_verification_send',
        );

        if (! $recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message'],
            );

            return;
        }

        /*
         * TicketService should deliberately return a generic
         * result so this endpoint cannot enumerate accounts/tickets.
         */
        $sent = $ticketService->sendGuestTicketVerificationCode(
            email: $this->email,
            ipAddress: request()->ip(),
        );

        if (! $sent) {
            $this->addError(
                'form',
                'Please wait a moment before requesting another verification code.',
            );

            return;
        }

        $this->emailVerificationSent = true;

        $this->verificationCode = '';

        $this->recaptchaToken = '';

        $this->resetErrorBag([
            'email',
            'recaptchaToken',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Verify email
    |--------------------------------------------------------------------------
    */

    public function prepareVerifyEmail(): void
    {
        $this->resetValidation([
            'form',
            'recaptchaToken',
        ]);

        $this->validate([
            'email' => $this->emailRules(),

            'verificationCode' => $this->verificationRules(),
        ]);

        /*
         * Spatie honeypot + minimum-time protection.
         */
        $this->protectAgainstSpam();

        $this->dispatch(
            'execute-ticket-verify-recaptcha',
        );
    }

    public function verifyEmail(
        string $recaptchaToken,
        TicketService $ticketService,
    ): void {
        $this->recaptchaToken = $recaptchaToken;

        /*
         * Honeypot protection.
         */
        $this->protectAgainstSpam();

        /*
         * Validate all security-sensitive inputs.
         */
        $this->validate([
            'email' => $this->emailRules(),

            'verificationCode' => $this->verificationRules(),

            'recaptchaToken' => [
                'bail',
                'required',
                'string',
                'max:4096',
            ],
        ]);

        /*
         * Aggressive per-email verification limit.
         */
        $this->ensureRateLimit(
            action: 'verify-email',
            maxAttempts: 5,
            decaySeconds: 900,
            value: $this->email,
        );

        /*
         * Independent IP limit.
         */
        $this->ensureRateLimit(
            action: 'verify-email-ip',
            maxAttempts: 15,
            decaySeconds: 900,
        );

        /*
         * Verify reCAPTCHA.
         */
        $recaptcha = $this->verifyRecaptcha(
            token: $recaptchaToken,
            expectedAction: 'ticket_verification_verify',
        );

        if (! $recaptcha['success']) {
            $this->addError(
                'form',
                $recaptcha['message'],
            );

            return;
        }

        /*
         * TicketService MUST perform the actual authentication.
         *
         * It should:
         *
         * - securely compare the verification code
         * - enforce expiration
         * - enforce its own attempt limit
         * - invalidate the code after successful use
         * - ensure tickets belong to the verified email
         * - establish whatever server-side verification state is required
         */
        $tickets = $ticketService->verifyGuestTicketVerificationCode(
            email: $this->email,
            code: $this->verificationCode,
            ipAddress: request()->ip(),
        );

        if (! $tickets) {
            $this->addError(
                'verificationCode',
                'That verification code is invalid or has expired.',
            );

            return;
        }

        $this->recaptchaToken = '';

        /*
         * These IDs are UI state only.
         *
         * They are NOT authorization.
         */
        $this->verifiedTicketIds = collect($tickets)
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id,
            )
            ->values()
            ->all();

        /*
         * If exactly one ticket exists, redirect immediately.
         */
        if (count($tickets) === 1) {
            $ticket = $tickets[0];

            /*
             * The service generates/rotates the secret token server-side.
             */
            $token = $ticketService->rotateGuestTicketToken(
                ticket: $ticket,
            );

            $ticketService->grantGuestTicketAccess(
                ticket: $ticket,
                minutes: 60,
            );

            $this->redirectRoute(
                'tickets.show',
                [
                    'ticketNumber' => $ticket->ticket_number,
                    'token' => $token,
                ],
            );

            return;
        }

        /*
         * Multiple tickets.
         */
        $this->showTicketSelection = true;

        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------------
    | Select ticket
    |--------------------------------------------------------------------------
    */

    public function selectTicket(
        int $ticketId,
        TicketService $ticketService,
    ): void {
        /*
         * The Livewire property is client-controlled.
         *
         * It is okay to use this check as a UI sanity check,
         * but it is NOT an authorization boundary.
         */
        if (! in_array(
            $ticketId,
            $this->verifiedTicketIds,
            true,
        )) {
            abort(403);
        }

        /*
         * The service MUST independently establish that:
         *
         * - this ticket belongs to the verified email
         * - the email verification is still valid
         * - the ticket is accessible
         * - the ticket has not been revoked
         */
        $ticket = $ticketService->findVerifiedEmailTicket(
            email: $this->email,
            ticketId: $ticketId,
        );

        if (! $ticket) {
            abort(404);
        }

        /*
         * Generate a fresh access token.
         */
        $token = $ticketService->rotateGuestTicketToken(
            ticket: $ticket,
        );

        /*
         * Grant short-lived access.
         */
        $ticketService->grantGuestTicketAccess(
            ticket: $ticket,
            minutes: 60,
        );

        $this->redirectRoute(
            'tickets.show',
            [
                'ticketNumber' => $ticket->ticket_number,
                'token' => $token,
            ],
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Change email
    |--------------------------------------------------------------------------
    */

    public function changeEmail(): void
    {
        $this->emailVerificationSent = false;

        $this->showTicketSelection = false;

        $this->verificationCode = '';

        $this->recaptchaToken = '';

        $this->verifiedTicketIds = [];

        $this->resetErrorBag();

        /*
         * Generate a fresh honeypot payload.
         */
        $this->extraFields = new HoneypotData();
    }

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA
    |--------------------------------------------------------------------------
    */

    protected function verifyRecaptcha(
        string $token,
        string $expectedAction,
    ): array {
        /*
         * Reject obviously invalid/oversized input before making
         * an external request.
         */
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

        /*
         * Google request failed.
         *
         * Do not log the response body.
         */
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

        /*
         * Token validity.
         */
        if (($data['success'] ?? false) !== true) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        /*
         * Action validation.
         */
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

        /*
         * Score must exist and be numeric.
         */
        $score = $data['score'] ?? null;

        if (! is_numeric($score)) {
            return [
                'success' => false,
                'message' => 'The security verification failed. Please try again.',
            ];
        }

        $score = (float) $score;

        /*
         * Valid Google v3 score is 0.0 - 1.0.
         */
        if ($score < 0.5 || $score > 1.0) {
            return [
                'success' => false,
                'message' => 'We could not verify that this request came from a real visitor. Please try again.',
            ];
        }

        /*
         * Optional hostname validation.
         */
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

    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    protected function resetFormState(): void
    {
        $this->email = '';

        $this->verificationCode = '';

        $this->recaptchaToken = '';

        $this->emailVerificationSent = false;

        $this->verifiedTicketIds = [];

        $this->showTicketSelection = false;

        $this->extraFields = new HoneypotData();

        $this->resetValidation();
    }

    public function addError($name, $message)
    {
        parent::addError($name, $message);
    }
    
    public function render()
    {
        return view(
            'livewire.home.tickets.ticket-search',
        )->layout(
            'layouts.home_layout',
        );
    }
}
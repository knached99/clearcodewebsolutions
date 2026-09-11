<?php

namespace App\Livewire\Home\Tickets;

use App\Services\TicketService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;
use Throwable;

class TicketSearch extends Component
{
    use UsesSpamProtection;

    private const RECAPTCHA_MAX_LENGTH = 4096;

    private const SEND_EMAIL_MAX_ATTEMPTS = 3;
    private const SEND_EMAIL_DECAY_SECONDS = 900;

    private const SEND_IP_MAX_ATTEMPTS = 10;
    private const SEND_IP_DECAY_SECONDS = 900;

    private const VERIFY_EMAIL_MAX_ATTEMPTS = 5;
    private const VERIFY_EMAIL_DECAY_SECONDS = 900;

    private const VERIFY_IP_MAX_ATTEMPTS = 15;
    private const VERIFY_IP_DECAY_SECONDS = 900;

    private const RECAPTCHA_MIN_SCORE = 0.5;

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
     * Never use these IDs as the authorization boundary.
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
                'max:' . self::RECAPTCHA_MAX_LENGTH,
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

    protected function recaptchaRules(): array
    {
        return [
            'bail',
            'required',
            'string',
            'max:' . self::RECAPTCHA_MAX_LENGTH,
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

        return sprintf(
            'ticket-search:%s:%s:%s',
            $action,
            hash('sha256', $ip),
            hash('sha256', $normalizedValue),
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

        if (! RateLimiter::tooManyAttempts(
            $key,
            $maxAttempts,
        )) {
            return;
        }

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

    protected function hitRateLimit(
        string $action,
        int $decaySeconds,
        ?string $value = null,
    ): void {
        RateLimiter::hit(
            $this->rateLimitKey(
                action: $action,
                value: $value,
            ),
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
         * Honeypot + minimum-time protection happens before
         * requesting a reCAPTCHA token.
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
         * Validate cheap inputs before doing external work.
         */
        $this->validate([
            'email' => $this->emailRules(),
            'recaptchaToken' => $this->recaptchaRules(),
        ]);

        /*
         * Check rate limits BEFORE the external reCAPTCHA request,
         * but do not consume an attempt until reCAPTCHA succeeds.
         */
        $this->ensureRateLimit(
            action: 'send-verification',
            maxAttempts: self::SEND_EMAIL_MAX_ATTEMPTS,
            decaySeconds: self::SEND_EMAIL_DECAY_SECONDS,
            value: $this->email,
        );

        $this->ensureRateLimit(
            action: 'send-verification-ip',
            maxAttempts: self::SEND_IP_MAX_ATTEMPTS,
            decaySeconds: self::SEND_IP_DECAY_SECONDS,
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
         * Consume the rate-limit attempts only after the security
         * check succeeds.
         */
        $this->hitRateLimit(
            action: 'send-verification',
            decaySeconds: self::SEND_EMAIL_DECAY_SECONDS,
            value: $this->email,
        );

        $this->hitRateLimit(
            action: 'send-verification-ip',
            decaySeconds: self::SEND_IP_DECAY_SECONDS,
        );

        /*
         * TicketService deliberately returns a generic result so
         * this endpoint cannot enumerate tickets/accounts.
         */
        $sent = $ticketService->sendGuestTicketVerificationCode(
            email: $this->email,
            ipAddress: request()->ip() ?? 'unknown',
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
         * Honeypot + minimum-time protection.
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
         * Honeypot + minimum-time protection.
         */
        $this->protectAgainstSpam();

        /*
         * Validate all security-sensitive inputs.
         */
        $this->validate([
            'email' => $this->emailRules(),
            'verificationCode' => $this->verificationRules(),
            'recaptchaToken' => $this->recaptchaRules(),
        ]);

        /*
         * Check rate limits before reCAPTCHA, but only consume
         * attempts after reCAPTCHA succeeds.
         */
        $this->ensureRateLimit(
            action: 'verify-email',
            maxAttempts: self::VERIFY_EMAIL_MAX_ATTEMPTS,
            decaySeconds: self::VERIFY_EMAIL_DECAY_SECONDS,
            value: $this->email,
        );

        $this->ensureRateLimit(
            action: 'verify-email-ip',
            maxAttempts: self::VERIFY_IP_MAX_ATTEMPTS,
            decaySeconds: self::VERIFY_IP_DECAY_SECONDS,
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
         * Consume the application-level rate limits after
         * the external security check succeeds.
         */
        $this->hitRateLimit(
            action: 'verify-email',
            decaySeconds: self::VERIFY_EMAIL_DECAY_SECONDS,
            value: $this->email,
        );

        $this->hitRateLimit(
            action: 'verify-email-ip',
            decaySeconds: self::VERIFY_IP_DECAY_SECONDS,
        );

        /*
         * TicketService performs the actual authentication.
         *
         * It is responsible for:
         *
         * - securely comparing the verification code
         * - enforcing expiration
         * - enforcing its own attempt limit
         * - invalidating the code after successful use
         * - ensuring tickets belong to the verified email
         * - establishing the server-side verification state
         */
        $tickets = $ticketService->verifyGuestTicketVerificationCode(
            email: $this->email,
            code: $this->verificationCode,
            ipAddress: request()->ip() ?? 'unknown',
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
                fn ($id): int => (int) $id,
            )
            ->values()
            ->all();

        /*
         * If exactly one ticket exists, redirect immediately.
         */
        if (count($tickets) === 1) {
            $ticket = $tickets[0];

            /*
             * Generate/rotate the secret token server-side.
             */
            $token = $ticketService->rotateGuestTicketToken(
                ticket: $ticket,
            );

            /*
             * Grant short-lived server-side access.
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
         * This property is client-controlled.
         *
         * It is only a UI sanity check and must never be the
         * authorization boundary.
         */
        if (! in_array(
            $ticketId,
            $this->verifiedTicketIds,
            true,
        )) {
            abort(403);
        }

        /*
         * TicketService independently verifies that this ticket:
         *
         * - belongs to the verified email
         * - is still accessible
         * - has not been revoked
         * - satisfies the server-side verification requirements
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
         * Grant short-lived server-side access.
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
         * Reject obviously invalid input before making an
         * external request.
         */
        if (
            $token === ''
            || strlen($token) > self::RECAPTCHA_MAX_LENGTH
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
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'We could not complete the security check. Please try again.',
            ];
        }

        /*
         * Google request failed.
         *
         * Do not log the response body because it can contain
         * security-sensitive information.
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
         * Action validation prevents a token generated for one
         * action from being reused for another.
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
        if (
            $score < self::RECAPTCHA_MIN_SCORE
            || $score > 1.0
        ) {
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

    public function render()
    {
        return view(
            'livewire.home.tickets.ticket-search',
        )->layout(
            'layouts.home_layout',
        );
    }
}
<?php

namespace App\Livewire\Home\Tickets;

use App\Models\Ticket;
use App\Services\RecaptchaService;
use App\Services\TicketService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

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

    private const VERIFIED_TICKETS_SESSION_KEY =
        'ticket_search.verified_ticket_ids';

    private const VERIFIED_EMAIL_SESSION_KEY =
        'ticket_search.verified_email';

    public string $email = '';

    public string $verificationCode = '';

    public HoneypotData $extraFields;

    public bool $emailVerificationSent = false;

    /**
     * UI state only.
     *
     * These IDs are NOT the authorization boundary.
     * The session is authoritative.
     */
    public array $verifiedTicketIds = [];

    public bool $showTicketSelection = false;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();

        $this->restoreVerifiedTicketState();
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

    protected function validateRecaptchaToken(
        string $token,
    ): void {
        Validator::make(
            [
                'recaptchaToken' => $token,
            ],
            [
                'recaptchaToken' => $this->recaptchaRules(),
            ],
            [
                'recaptchaToken.required' =>
                    'Please wait a moment and try again.',
            ],
        )->validate();
    }

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
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
    | Send Verification Code
    |--------------------------------------------------------------------------
    */

    public function prepareSendVerificationCode(): void
    {
        $this->resetValidation([
            'form',
            'email',
        ]);

        $this->validate([
            'email' => $this->emailRules(),
        ]);

        $this->dispatch(
            'execute-ticket-send-recaptcha',
        );
    }

    /**
     * Security order:
     *
     * 1. Rate-limit check
     * 2. Rate-limit hit
     * 3. Honeypot
     * 4. Cheap Laravel validation
     * 5. reCAPTCHA
     * 6. TicketService/business validation
     * 7. Database
     */
    public function sendVerificationCode(
        string $recaptchaToken,
        TicketService $ticketService,
        RecaptchaService $recaptchaService,
    ): void {
        /*
         * ---------------------------------------------------------
         * 1. RATE LIMIT CHECK
         * ---------------------------------------------------------
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
         * ---------------------------------------------------------
         * 2. RATE LIMIT HIT
         * ---------------------------------------------------------
         *
         * Consume attempts BEFORE contacting Google.
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

        $this->validate([
            'email' => $this->emailRules(),
        ]);

        $this->validateRecaptchaToken(
            $recaptchaToken,
        );

        /*
         * ---------------------------------------------------------
         * 5. RECAPTCHA
         * ---------------------------------------------------------
         */

        $recaptcha = $recaptchaService->verifyRecaptcha(
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
         * ---------------------------------------------------------
         * 6. BUSINESS VALIDATION / SERVICE
         * ---------------------------------------------------------
         */

        $sent = $ticketService->sendGuestTicketVerificationCode(
            email: strtolower(trim($this->email)),
            ipAddress: request()->ip() ?? 'unknown',
        );

        /*
         * ---------------------------------------------------------
         * 7. DATABASE
         * ---------------------------------------------------------
         *
         * TicketService handles the verification-code persistence.
         */

        if (! $sent) {
            $this->addError(
                'form',
                'Please wait a moment before requesting another verification code.',
            );

            return;
        }

        $this->clearVerifiedTicketState();

        $this->emailVerificationSent = true;

        $this->verificationCode = '';

        $this->resetErrorBag([
            'email',
            'form',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Email
    |--------------------------------------------------------------------------
    */

    public function prepareVerifyEmail(): void
    {
        $this->resetValidation([
            'form',
        ]);

        $this->validate([
            'email' => $this->emailRules(),
            'verificationCode' => $this->verificationRules(),
        ]);

        $this->dispatch(
            'execute-ticket-verify-recaptcha',
        );
    }

    /**
     * Security order:
     *
     * 1. Rate-limit check
     * 2. Rate-limit hit
     * 3. Honeypot
     * 4. Cheap Laravel validation
     * 5. reCAPTCHA
     * 6. TicketService/business validation
     * 7. Database
     */
    public function verifyEmail(
        string $recaptchaToken,
        TicketService $ticketService,
        RecaptchaService $recaptchaService,
    ): void {
        /*
         * ---------------------------------------------------------
         * 1. RATE LIMIT CHECK
         * ---------------------------------------------------------
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
         * ---------------------------------------------------------
         * 2. RATE LIMIT HIT
         * ---------------------------------------------------------
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

        $this->validate([
            'email' => $this->emailRules(),
            'verificationCode' => $this->verificationRules(),
        ]);

        $this->validateRecaptchaToken(
            $recaptchaToken,
        );

        /*
         * ---------------------------------------------------------
         * 5. RECAPTCHA
         * ---------------------------------------------------------
         */

        $recaptcha = $recaptchaService->verifyRecaptcha(
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
         * ---------------------------------------------------------
         * 6. BUSINESS VALIDATION / SERVICE
         * ---------------------------------------------------------
         */

        $tickets = $ticketService->verifyGuestTicketVerificationCode(
            email: strtolower(trim($this->email)),
            code: $this->verificationCode,
            ipAddress: request()->ip() ?? 'unknown',
        );

        /*
         * ---------------------------------------------------------
         * 7. DATABASE / VERIFIED RESULT
         * ---------------------------------------------------------
         */

        if (! $tickets) {
            $this->addError(
                'verificationCode',
                'That verification code is invalid or has expired.',
            );

            return;
        }

        /*
         * Store only validated ticket IDs in the session.
         */

        $ticketIds = collect($tickets)
            ->pluck('id')
            ->filter(
                fn ($id): bool =>
                    is_int($id)
                    || (
                        is_string($id)
                        && ctype_digit($id)
                    ),
            )
            ->map(
                fn ($id): int => (int) $id,
            )
            ->filter(
                fn (int $id): bool => $id > 0,
            )
            ->unique()
            ->values()
            ->all();

        if ($ticketIds === []) {
            $this->addError(
                'form',
                'No tickets were found for this email address.',
            );

            return;
        }

        session()->put(
            self::VERIFIED_TICKETS_SESSION_KEY,
            $ticketIds,
        );

        session()->put(
            self::VERIFIED_EMAIL_SESSION_KEY,
            strtolower(trim($this->email)),
        );

        $this->verifiedTicketIds = $ticketIds;

        /*
         * Exactly one ticket:
         * immediately generate its access token.
         */

        if (count($tickets) === 1) {
            $ticket = $tickets[0];

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
         * Multiple tickets:
         * show the selection UI.
         */

        $this->showTicketSelection = true;

        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------------
    | Select Ticket
    |--------------------------------------------------------------------------
    */

    public function selectTicket(
        int $ticketId,
        TicketService $ticketService,
    ): void {
        /*
         * Never trust the client-controlled Livewire array.
         *
         * The session is the authorization boundary.
         */

        $verifiedTicketIds = $this->getVerifiedTicketIds();

        if (! in_array(
            $ticketId,
            $verifiedTicketIds,
            true,
        )) {
            abort(403);
        }

        $verifiedEmail = $this->getVerifiedEmail();

        if ($verifiedEmail === null) {
            abort(404);
        }

        /*
         * Confirm ticket ownership again against the database.
         */

        $ticket = $ticketService->findVerifiedEmailTicket(
            email: $verifiedEmail,
            ticketId: $ticketId,
        );

        if (! $ticket) {
            abort(404);
        }

        $token = $ticketService->rotateGuestTicketToken(
            ticket: $ticket,
        );

        $ticketService->grantGuestTicketAccess(
            ticket: $ticket,
            minutes: 60,
        );

        $this->clearVerifiedTicketState();

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
    | Change Email
    |--------------------------------------------------------------------------
    */

    public function changeEmail(): void
    {
        $this->clearVerifiedTicketState();

        $this->email = '';

        $this->emailVerificationSent = false;

        $this->showTicketSelection = false;

        $this->verificationCode = '';

        $this->verifiedTicketIds = [];

        $this->resetErrorBag();

        $this->extraFields = new HoneypotData();
    }

    /*
    |--------------------------------------------------------------------------
    | Server-side Verification State
    |--------------------------------------------------------------------------
    */

    protected function restoreVerifiedTicketState(): void
    {
        $ticketIds = session(
            self::VERIFIED_TICKETS_SESSION_KEY,
            [],
        );

        $verifiedEmail = session(
            self::VERIFIED_EMAIL_SESSION_KEY,
        );

        if (
            ! is_array($ticketIds)
            || ! is_string($verifiedEmail)
            || trim($verifiedEmail) === ''
        ) {
            $this->clearVerifiedTicketState();

            return;
        }

        $ticketIds = collect($ticketIds)
            ->filter(
                fn ($id): bool =>
                    is_int($id)
                    || (
                        is_string($id)
                        && ctype_digit($id)
                    ),
            )
            ->map(
                fn ($id): int => (int) $id,
            )
            ->filter(
                fn (int $id): bool => $id > 0,
            )
            ->unique()
            ->values()
            ->all();

        if ($ticketIds === []) {
            $this->clearVerifiedTicketState();

            return;
        }

        $this->email = strtolower(
            trim($verifiedEmail),
        );

        $this->verifiedTicketIds = $ticketIds;

        $this->showTicketSelection = true;
    }

    protected function getVerifiedTicketIds(): array
    {
        $ticketIds = session(
            self::VERIFIED_TICKETS_SESSION_KEY,
            [],
        );

        if (! is_array($ticketIds)) {
            return [];
        }

        return collect($ticketIds)
            ->filter(
                fn ($id): bool =>
                    is_int($id)
                    || (
                        is_string($id)
                        && ctype_digit($id)
                    ),
            )
            ->map(
                fn ($id): int => (int) $id,
            )
            ->filter(
                fn (int $id): bool => $id > 0,
            )
            ->unique()
            ->values()
            ->all();
    }

    protected function getVerifiedEmail(): ?string
    {
        $email = session(
            self::VERIFIED_EMAIL_SESSION_KEY,
        );

        if (
            ! is_string($email)
            || trim($email) === ''
        ) {
            return null;
        }

        return strtolower(
            trim($email),
        );
    }

    protected function clearVerifiedTicketState(): void
    {
        session()->forget([
            self::VERIFIED_TICKETS_SESSION_KEY,
            self::VERIFIED_EMAIL_SESSION_KEY,
        ]);

        $this->verifiedTicketIds = [];

        $this->showTicketSelection = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    protected function resetFormState(): void
    {
        $this->clearVerifiedTicketState();

        $this->email = '';

        $this->verificationCode = '';

        $this->emailVerificationSent = false;

        $this->verifiedTicketIds = [];

        $this->showTicketSelection = false;

        $this->extraFields = new HoneypotData();

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $tickets = collect();

        $verifiedTicketIds = $this->getVerifiedTicketIds();

        $verifiedEmail = $this->getVerifiedEmail();

        /*
         * Only load tickets that were authenticated through
         * the server-side verification flow.
         */

        if (
            $verifiedTicketIds !== []
            && $verifiedEmail !== null
        ) {
            $tickets = Ticket::query()
                ->whereIn(
                    'id',
                    $verifiedTicketIds,
                )
                ->whereRaw(
                    'LOWER(email) = ?',
                    [$verifiedEmail],
                )
                ->latest('last_activity_at')
                ->get();
        }

        return view(
            'livewire.home.tickets.ticket-search',
            [
                'tickets' => $tickets,
            ],
        )->layout(
            'layouts.home_layout',
        );
    }
}
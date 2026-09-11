<?php

namespace App\Livewire\Home\Tickets;

use App\Models\Ticket;
use App\Services\TicketService;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\RateLimiter;
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

    private const RECAPTCHA_MIN_SCORE = 0.5;

    private const VERIFIED_TICKETS_SESSION_KEY = 'ticket_search.verified_ticket_ids';

    private const VERIFIED_EMAIL_SESSION_KEY = 'ticket_search.verified_email';

    public string $email = '';

    public string $verificationCode = '';

    public HoneypotData $extraFields;

    public string $recaptchaToken = '';

    public bool $emailVerificationSent = false;

    /**
     * UI state only.
     *
     * These IDs are intentionally NOT used as the authorization
     * boundary. The authoritative copy is kept in the session.
     */
    public array $verifiedTicketIds = [];

    public bool $showTicketSelection = false;

    public function mount(): void
    {
        $this->extraFields = new HoneypotData();

        /*
         * Restore server-side verification state if it still exists.
         */
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

        $this->protectAgainstSpam();

        $this->dispatch(
            'execute-ticket-send-recaptcha',
        );
    }

   public function sendVerificationCode(
    string $recaptchaToken,
    TicketService $ticketService,
    RecaptchaService $recaptchaService,
): void {
    $this->recaptchaToken = $recaptchaToken;

    $this->protectAgainstSpam();

    $this->validate([
        'email' => $this->emailRules(),
        'recaptchaToken' => $this->recaptchaRules(),
    ]);

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

    $this->hitRateLimit(
        action: 'send-verification',
        decaySeconds: self::SEND_EMAIL_DECAY_SECONDS,
        value: $this->email,
    );

    $this->hitRateLimit(
        action: 'send-verification-ip',
        decaySeconds: self::SEND_IP_DECAY_SECONDS,
    );

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

    $this->clearVerifiedTicketState();

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

        $this->protectAgainstSpam();

        $this->dispatch(
            'execute-ticket-verify-recaptcha',
        );
    }

    public function verifyEmail(
        string $recaptchaToken,
        TicketService $ticketService,
        RecaptchaService $recaptchaService,
    ): void {
        $this->recaptchaToken = $recaptchaToken;

        $this->protectAgainstSpam();

        $this->validate([
            'email' => $this->emailRules(),
            'verificationCode' => $this->verificationRules(),
            'recaptchaToken' => $this->recaptchaRules(),
        ]);

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

        $this->hitRateLimit(
            action: 'verify-email',
            decaySeconds: self::VERIFY_EMAIL_DECAY_SECONDS,
            value: $this->email,
        );

        $this->hitRateLimit(
            action: 'verify-email-ip',
            decaySeconds: self::VERIFY_IP_DECAY_SECONDS,
        );

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
         * Store the authoritative verification state server-side.
         *
         * The Livewire property below remains UI state only.
         */
        $ticketIds = collect($tickets)
            ->pluck('id')
            ->map(
                fn ($id): int => (int) $id,
            )
            ->values()
            ->all();

        session()->put(
            self::VERIFIED_TICKETS_SESSION_KEY,
            $ticketIds,
        );

        session()->put(
            self::VERIFIED_EMAIL_SESSION_KEY,
            strtolower(trim($this->email)),
        );

        /*
         * UI state.
         */
        $this->verifiedTicketIds = $ticketIds;

        /*
         * If exactly one ticket exists, redirect immediately.
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
         * Never trust the client-controlled Livewire array.
         *
         * Read the authoritative IDs from the session instead.
         */
        $verifiedTicketIds = $this->getVerifiedTicketIds();

        if (! in_array(
            $ticketId,
            $verifiedTicketIds,
            true,
        )) {
            abort(403);
        }

        /*
         * The email stored in the session is authoritative.
         */
        $verifiedEmail = $this->getVerifiedEmail();

        if ($verifiedEmail === null) {
            abort(404);
        }

        /*
         * Confirm that this ticket still belongs to the verified email.
         */
        $ticket = $ticketService->findVerifiedEmailTicket(
            email: $verifiedEmail,
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

        /*
         * The temporary email-verification selection state is no
         * longer needed once this ticket has its own session access.
         */
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
    | Change email
    |--------------------------------------------------------------------------
    */

    public function changeEmail(): void
    {
        $this->clearVerifiedTicketState();

        $this->email = '';
        $this->emailVerificationSent = false;
        $this->showTicketSelection = false;
        $this->verificationCode = '';
        $this->recaptchaToken = '';
        $this->verifiedTicketIds = [];

        $this->resetErrorBag();

        $this->extraFields = new HoneypotData();
    }

    /*
    |--------------------------------------------------------------------------
    | Server-side verification state
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
            || $verifiedEmail === null
            || ! is_string($verifiedEmail)
        ) {
            $this->clearVerifiedTicketState();

            return;
        }

        $ticketIds = collect($ticketIds)
            ->filter(
                fn ($id): bool => is_int($id)
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

        $this->email = strtolower(trim($verifiedEmail));
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

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return strtolower(trim($email));
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
        $this->recaptchaToken = '';
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
         * Only load tickets that were authenticated by the
         * server-side email verification flow.
         */
        if (
            $verifiedTicketIds !== []
            && $verifiedEmail !== null
        ) {
            $tickets = Ticket::query()
                ->whereIn('id', $verifiedTicketIds)
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
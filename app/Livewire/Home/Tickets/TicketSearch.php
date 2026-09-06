<?php

namespace App\Livewire\Home\Tickets;

use App\Services\TicketService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class TicketSearch extends Component
{
    public string $email = '';

    public string $verificationCode = '';

    public bool $emailVerificationSent = false;

    public array $verifiedTicketIds = [];

    public bool $showTicketSelection = false;

    /**
     * |--------------------------------------------------------------------------
     * | Rate Limiting
     * |--------------------------------------------------------------------------
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
     * |--------------------------------------------------------------------------
     * | Send Email Verification Code
     * |--------------------------------------------------------------------------
     */
    public function sendVerificationCode(
        TicketService $ticketService
    ): void {
        $this->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        /**
         * 3 requests per email/IP every 15 minutes.
         */
        $this->ensureRateLimit(
            action: 'send-verification',
            maxAttempts: 3,
            decaySeconds: 900,
            value: $this->email,
        );

        $sent = $ticketService->sendGuestTicketVerificationCode(
            email: $this->email,
            ipAddress: request()->ip(),
        );

        if (! $sent) {
            $this->addError(
                'email',
                'Please wait a moment before requesting another verification code.'
            );

            return;
        }

        $this->emailVerificationSent = true;

        $this->resetErrorBag('email');

        $this->verificationCode = '';
    }

    /**
     * |--------------------------------------------------------------------------
     * | Verify Email Code
     * |--------------------------------------------------------------------------
     */
    public function verifyEmail(
        TicketService $ticketService
    ): void {
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

        /**
         * 5 verification attempts per email/IP every 15 minutes.
         */
        $this->ensureRateLimit(
            action: 'verify-email',
            maxAttempts: 5,
            decaySeconds: 900,
            value: $this->email,
        );

        $tickets = $ticketService->verifyGuestTicketVerificationCode(
            email: $this->email,
            code: $this->verificationCode,
            ipAddress: request()->ip(),
        );

        if (! $tickets) {
            $this->addError(
                'verificationCode',
                'That verification code is invalid or has expired.'
            );

            return;
        }

        $this->verifiedTicketIds = collect($tickets)
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values()
            ->all();

        /**
         * -------------------------------------------------------------
         * One ticket
         * -------------------------------------------------------------
         *
         * Email verification has authenticated ownership of the email
         * address.
         *
         * Generate a fresh real guest token for this ticket.
         *
         * The raw token is never stored in the database.
         */
        if (count($this->verifiedTicketIds) === 1) {
            $ticket = $tickets[0];

            $token = $ticketService->rotateGuestTicketToken(
                ticket: $ticket,
            );

            /**
             * Also establish the guest session.
             */
            $ticketService->grantGuestTicketAccess(
                ticket: $ticket,
                minutes: 60,
            );

            /**
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

        /**
         * -------------------------------------------------------------
         * Multiple tickets
         * -------------------------------------------------------------
         *
         * Let the customer select which ticket they want to access.
         */
        $this->showTicketSelection = true;

        $this->resetErrorBag();
    }

    /**
     * |--------------------------------------------------------------------------
     * | Select Ticket After Email Verification
     * |--------------------------------------------------------------------------
     */
    public function selectTicket(
        int $ticketId,
        TicketService $ticketService
    ): void {
        if (! in_array(
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

        if (! $ticket) {
            abort(404);
        }

        /**
         * Generate a fresh real guest token.
         *
         * This token is the same credential stored by hash in
         * tickets.guest_token_hash.
         */
        $token = $ticketService->rotateGuestTicketToken(
            ticket: $ticket,
        );

        /**
         * Establish guest session access.
         */
        $ticketService->grantGuestTicketAccess(
            ticket: $ticket,
            minutes: 60,
        );

        /**
         * Redirect using the REAL 64-character token.
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
     * |--------------------------------------------------------------------------
     * | Change Email
     * |--------------------------------------------------------------------------
     */
    public function changeEmail(): void
    {
        $this->emailVerificationSent = false;

        $this->showTicketSelection = false;

        $this->verificationCode = '';

        $this->verifiedTicketIds = [];

        $this->resetErrorBag();
    }

    /**
     * |--------------------------------------------------------------------------
     * | Render
     * |--------------------------------------------------------------------------
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
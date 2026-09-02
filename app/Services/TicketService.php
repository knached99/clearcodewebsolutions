<?php

namespace App\Services;

use App\Mail\GuestTicketVerificationCodeMail;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\TicketAccessToken;
use App\Models\TicketVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TicketService
{
    /*
    |--------------------------------------------------------------------------
    | Ticket Creation
    |--------------------------------------------------------------------------
    */

    public function createTicket(array $data): array
    {
        $team = Team::where('is_personal', true)->firstOrFail();

        return DB::transaction(function () use ($data, $team) {
            /*
             * This is the original permanent/private guest token.
             *
             * The raw token is returned once to the caller.
             * Only its SHA-256 hash is stored in the database.
             */
            $token = bin2hex(random_bytes(32));

            $ticket = Ticket::create([
                'team_id' => $team->id,
                'ticket_number' => $this->generateTicketNumber(),
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'status' => 'open',
                'priority' => 'normal',
                'guest_token_hash' => hash('sha256', $token),
                'last_activity_at' => now(),
            ]);

            $ticket->messages()->create([
                'author_type' => 'guest',
                'author_name' => $data['name'],
                'message' => $data['message'],
            ]);

            return [
                'ticket' => $ticket,
                'token' => $token,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Original Ticket Token Authentication
    |--------------------------------------------------------------------------
    */

    public function findGuestTicket(
        string $ticketNumber,
    ): ?Ticket {
        $ticketNumber = trim($ticketNumber);

        if ($ticketNumber === '') {
            return null;
        }

        $ticket = Ticket::query()
            ->where('ticket_number', $ticketNumber)
            ->first();

        if (! $ticket) {
            return null;
        }

        return $ticket;
    }

    public function authenticateGuestTicket(
        string $ticketNumber,
        string $token,
        string $ipAddress
    ): ?Ticket {
        $ticketNumber = trim($ticketNumber);

        $ipKey = 'guest-ticket-search:ip:' . $ipAddress;

        $ticketKey = 'guest-ticket-search:ticket:' . hash(
            'sha256',
            Str::lower($ticketNumber)
        );

        if (
            RateLimiter::tooManyAttempts($ipKey, 20) ||
            RateLimiter::tooManyAttempts($ticketKey, 5)
        ) {
            return null;
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($ticketKey, 60);

        $ticket = $this->findGuestTicket(
            $ticketNumber,
            $token
        );

        if (! $ticket) {
            return null;
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($ticketKey);

        return $ticket;
    }

    /*
    |--------------------------------------------------------------------------
    | Session Guest Access
    |--------------------------------------------------------------------------
    */

    public function grantGuestTicketAccess(
        Ticket $ticket,
        int $minutes = 60
    ): void {
        session()->put(
            'guest_ticket_access.' . $ticket->id,
            now()->addMinutes($minutes)->timestamp
        );
    }

    public function hasGuestTicketAccess(Ticket $ticket): bool
    {
        $expiresAt = session(
            'guest_ticket_access.' . $ticket->id
        );

        if (! $expiresAt) {
            return false;
        }

        if ((int) $expiresAt < now()->timestamp) {
            $this->revokeGuestTicketAccess($ticket);

            return false;
        }

        return true;
    }

    public function requireGuestTicketAccess(Ticket $ticket): void
    {
        $expiresAt = session(
            "guest_ticket_access.{$ticket->id}"
        );

        if (! $expiresAt) {
            abort(404);
        }

        if ((int) $expiresAt < now()->timestamp) {
            session()->forget(
                "guest_ticket_access.{$ticket->id}"
            );

            abort(404);
        }
    }

    public function revokeGuestTicketAccess(Ticket $ticket): void
    {
        session()->forget(
            'guest_ticket_access.' . $ticket->id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Email Verification Code
    |--------------------------------------------------------------------------
    */

    public function sendGuestTicketVerificationCode(
        string $email,
        string $ipAddress,
    ): bool {
        $email = Str::lower(trim($email));

        if (
            $email === '' ||
            ! filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            return false;
        }

        $emailKey = 'guest-ticket-email:' . hash(
            'sha256',
            $email
        );

        $ipKey = 'guest-ticket-email-ip:' . $ipAddress;

        if (
            RateLimiter::tooManyAttempts($emailKey, 3) ||
            RateLimiter::tooManyAttempts($ipKey, 10)
        ) {
            return false;
        }

        RateLimiter::hit($emailKey, 900);
        RateLimiter::hit($ipKey, 900);

        /*
         * Always return success for a syntactically valid email.
         *
         * This prevents email enumeration.
         */
        $hasTicket = Ticket::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->exists();

        if (! $hasTicket) {
            return true;
        }

        /*
         * Invalidate previous unverified codes.
         */
        TicketVerification::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->update([
                'verified_at' => now(),
            ]);

        /*
         * Generate a cryptographically secure 6-digit code.
         */
        $code = (string) random_int(
            100000,
            999999
        );

        $verification = TicketVerification::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
        ]);

        /*
         * Plaintext code exists only in memory long enough
         * to send the email.
         *
         * It is never stored in the database.
         */
        Mail::to($email)->send(
            new GuestTicketVerificationCodeMail(
                code: $code,
                expiresAt: $verification->expires_at,
            )
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Email Code
    |--------------------------------------------------------------------------
    */

    public function verifyGuestTicketVerificationCode(
        string $email,
        string $code,
        string $ipAddress,
    ): ?array {
        $email = Str::lower(trim($email));
        $code = trim($code);

        if (
            $email === '' ||
            ! preg_match('/^\d{6}$/', $code)
        ) {
            return null;
        }

        $emailKey = 'guest-ticket-code:' . hash(
            'sha256',
            $email
        );

        $ipKey = 'guest-ticket-code-ip:' . $ipAddress;

        if (
            RateLimiter::tooManyAttempts($emailKey, 10) ||
            RateLimiter::tooManyAttempts($ipKey, 20)
        ) {
            return null;
        }

        RateLimiter::hit($emailKey, 900);
        RateLimiter::hit($ipKey, 900);

        $verification = TicketVerification::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            return null;
        }

        if ($verification->expires_at->isPast()) {
            return null;
        }

        if ($verification->attempts >= 5) {
            return null;
        }

        /*
         * Count every verification attempt.
         */
        $verification->increment('attempts');

        if (! Hash::check(
            $code,
            $verification->code_hash
        )) {
            return null;
        }

        $verification->update([
            'verified_at' => now(),
        ]);

        RateLimiter::clear($emailKey);
        RateLimiter::clear($ipKey);

        /*
         * Find all tickets belonging to this email.
         */
        $tickets = Ticket::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->latest('last_activity_at')
            ->get()
            ->all();

        if ($tickets === []) {
            return null;
        }

        return $tickets;
    }

    /*
    |--------------------------------------------------------------------------
    | Email-Verified Ticket
    |--------------------------------------------------------------------------
    */

    public function findVerifiedEmailTicket(
        string $email,
        int $ticketId,
    ): ?Ticket {
        $email = Str::lower(trim($email));

        if ($email === '') {
            return null;
        }

        return Ticket::query()
            ->whereKey($ticketId)
            ->whereRaw(
                'LOWER(email) = ?',
                [$email]
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Temporary URL Access Token
    |--------------------------------------------------------------------------
    */

    public function createEmailTicketAccessToken(
        Ticket $ticket,
        int $minutes = 60
    ): string {
        /*
         * Generate a completely separate token.
         *
         * This is NOT the original guest private key.
         */
        $token = bin2hex(random_bytes(32));

        TicketAccessToken::query()
            ->where('ticket_id', $ticket->id)
            ->delete();

        TicketAccessToken::create([
            'ticket_id' => $ticket->id,
            'token_hash' => hash(
                'sha256',
                $token
            ),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        return $token;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Temporary Email Access Token
    |--------------------------------------------------------------------------
    */

    public function findTicketByEmailAccessToken(
        string $ticketNumber,
        string $token
    ): ?Ticket {
        $ticketNumber = trim($ticketNumber);
        $token = trim($token);

        if (
            $ticketNumber === '' ||
            $token === ''
        ) {
            return null;
        }

        $tokenHash = hash(
            'sha256',
            $token
        );

        $accessToken = TicketAccessToken::query()
            ->with('ticket')
            ->where('token_hash', $tokenHash)
            ->whereHas(
                'ticket',
                function ($query) use ($ticketNumber) {
                    $query->where(
                        'ticket_number',
                        $ticketNumber
                    );
                }
            )
            ->first();

        if (! $accessToken) {
            return null;
        }

        if ($accessToken->expires_at->isPast()) {
            $accessToken->delete();

            return null;
        }

        return $accessToken->ticket;
    }

    /*
    |--------------------------------------------------------------------------
    | Ticket Number Generation
    |--------------------------------------------------------------------------
    */

    protected function generateTicketNumber(): string
    {
        do {
            $ticketNumber =
                'TCKT-' . strtoupper(
                    Str::random(8)
                );
        } while (
            Ticket::where(
                'ticket_number',
                $ticketNumber
            )->exists()
        );

        return $ticketNumber;
    }
}
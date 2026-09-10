<?php

namespace App\Services;

use App\Mail\GuestTicketVerificationCodeMail;
use App\Notifications\NewTicketNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\Team;
use App\Models\Ticket;
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
        $team = $this->resolveTicketTeam();

        return DB::transaction(function () use ($data, $team) {
            /*
             * Generate the raw guest token once.
             *
             * The raw token is returned to the caller so it can be
             * included in the customer's initial email.
             *
             * Only the SHA-256 hash is stored in the database.
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
                'last_activity_at' => now(),
                'guest_token_hash' => hash('sha256', $token),
            ]);

            $ticket->messages()->create([
                'author_type' => 'guest',
                'author_name' => $data['name'],
                'message' => $data['message'],
            ]);

            /*
             * Queue the team notification after the ticket has been
             * successfully committed to the database.
             *
             * This prevents the mail worker from attempting to process
             * a ticket that could still be rolled back.
             */
            DB::afterCommit(function () use ($ticket) {
                $this->notifyTeamsOfNewTicket($ticket);
            });

            return [
                'ticket' => $ticket,
                'token' => $token,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Ticket Team Resolution
    |--------------------------------------------------------------------------
    */

    protected function resolveTicketTeam(): Team
    {
        /*
         * Teams are guaranteed to exist.
         *
         * The first team by ID is always the primary team assigned to
         * newly-created tickets.
         */
        return Team::query()
            ->orderBy('id')
            ->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Team Notifications
    |--------------------------------------------------------------------------
    */

  protected function notifyTeamsOfNewTicket(Ticket $ticket): void
{
    $members = Team::query()
        ->with('members:id,email')
        ->orderBy('id')
        ->get()
        ->flatMap(
            fn (Team $team) => $team->members
        )
        ->unique('id')
        ->values();

    if ($members->isEmpty()) {
        return;
    }

    Notification::send(
        $members,
        new NewTicketNotification($ticket),
    );
}

    /*
    |--------------------------------------------------------------------------
    | Guest Ticket Lookup
    |--------------------------------------------------------------------------
    */

    public function findGuestTicket(string $ticketNumber): ?Ticket
    {
        $ticketNumber = trim($ticketNumber);

        if ($ticketNumber === '') {
            return null;
        }

        return Ticket::query()
            ->where('ticket_number', $ticketNumber)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Session Guest Access
    |--------------------------------------------------------------------------
    */

    public function grantGuestTicketAccess(
        Ticket $ticket,
        int $minutes = 60,
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
    | Guest Token Verification
    |--------------------------------------------------------------------------
    */

    public function verifyGuestTicketAccess(
        Ticket $ticket,
        string $token,
    ): bool {
        if (! preg_match('/^[a-fA-F0-9]{64}$/', $token)) {
            return false;
        }

        $tokenHash = hash('sha256', $token);

        /*
         * Check the original token stored on the ticket.
         */
        if (
            $ticket->guest_token_hash &&
            is_string($ticket->guest_token_hash) &&
            hash_equals(
                $ticket->guest_token_hash,
                $tokenHash
            )
        ) {
            return true;
        }

        /*
         * Check tokens created for notification/deep links.
         */
        return $ticket->accessTokens()
            ->where('token_hash', $tokenHash)
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Guest Ticket URL
    |--------------------------------------------------------------------------
    */

    public function createGuestTicketUrl(
        Ticket $ticket,
        string $token,
    ): string {
        if (! preg_match('/^[a-fA-F0-9]{64}$/', $token)) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        return rtrim(config('app.url'), '/') .
            '/tickets/' .
            $ticket->ticket_number .
            '/' .
            $token;
    }

    /*
    |--------------------------------------------------------------------------
    | Create Additional Guest Access Token
    |--------------------------------------------------------------------------
    */

    public function createGuestAccessToken(
        Ticket $ticket,
        int $minutes = 60 * 24 * 30,
    ): string {
        $token = bin2hex(random_bytes(32));

        $ticket->accessTokens()->create([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        return $token;
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

    /*
    |--------------------------------------------------------------------------
    | Rotate Guest Ticket Token
    |--------------------------------------------------------------------------
    */

    public function rotateGuestTicketToken(
        Ticket $ticket,
    ): string {
        $token = bin2hex(random_bytes(32));

        $ticket->forceFill([
            'guest_token_hash' => hash(
                'sha256',
                $token
            ),
        ])->save();

        return $token;
    }
}
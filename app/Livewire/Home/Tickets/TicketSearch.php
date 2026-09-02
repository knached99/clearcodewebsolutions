<?php

namespace App\Livewire\Home\Tickets;

use App\Services\TicketService;
use Livewire\Component;

class TicketSearch extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Ticket Number
    |--------------------------------------------------------------------------
    */

    public string $ticketNumber = '';

    /*
    |--------------------------------------------------------------------------
    | Email Authentication
    |--------------------------------------------------------------------------
    */

    public string $email = '';
    public string $verificationCode = '';
    public bool $emailVerificationSent = false;

    /*
    |--------------------------------------------------------------------------
    | Ticket Selection After Email Verification
    |--------------------------------------------------------------------------
    */

    public array $verifiedTicketIds = [];
    public bool $showTicketSelection = false;

    /*
    |--------------------------------------------------------------------------
    | Search by Ticket Number
    |--------------------------------------------------------------------------
    */

  public function search(
    TicketService $ticketService
): void {
    $this->validate([
        'ticketNumber' => [
            'required',
            'string',
            'max:100',
        ],
    ]);

    $ticket = $ticketService->findGuestTicketByNumber(
        ticketNumber: $this->ticketNumber,
    );

    if (! $ticket) {
        $this->addError(
            'ticketNumber',
            "We couldn't find a ticket with that number."
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate temporary access token
    |--------------------------------------------------------------------------
    */

    $token = $ticketService->createEmailTicketAccessToken(
        ticket: $ticket,
        minutes: 60,
    );

    /*
    |--------------------------------------------------------------------------
    | Grant session access
    |--------------------------------------------------------------------------
    */

    $ticketService->grantGuestTicketAccess(
        ticket: $ticket,
        minutes: 60,
    );

    /*
    |--------------------------------------------------------------------------
    | Redirect to tokenized ticket URL
    |--------------------------------------------------------------------------
    */

    $this->redirectRoute(
        'tickets.show',
        [
            'ticketNumber' => $ticket->ticket_number,
            'token' => $token,
        ]
    );
}

    /*
    |--------------------------------------------------------------------------
    | Send Email Verification Code
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Verify Email Code
    |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | One ticket
        |--------------------------------------------------------------------------
        */

        if (count($this->verifiedTicketIds) === 1) {
            $ticket = $tickets[0];

            $token = $ticketService->createEmailTicketAccessToken(
                ticket: $ticket,
                minutes: 60,
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
                ]
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple tickets
        |--------------------------------------------------------------------------
        */

        $this->showTicketSelection = true;

        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------------
    | Select Ticket After Email Verification
    |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | Generate temporary token for selected ticket
        |--------------------------------------------------------------------------
        */

        $token = $ticketService->createEmailTicketAccessToken(
            ticket: $ticket,
            minutes: 60,
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
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Change Email
    |--------------------------------------------------------------------------
    */

    public function changeEmail(): void
    {
        $this->emailVerificationSent = false;
        $this->showTicketSelection = false;
        $this->verificationCode = '';
        $this->verifiedTicketIds = [];

        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
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
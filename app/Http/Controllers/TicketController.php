<?php

namespace App\Http\Controllers;

use App\Services\TicketService;

class TicketController extends Controller
{
    public function show()
    {
        return response('ADMIN SHOW');
    }

    public function guestShow(
        TicketService $ticketService,
        string $ticketNumber,
        ?string $token = null,
    ) {
        $ticket = $ticketService->findGuestTicket(
            ticketNumber: $ticketNumber,
        );

        if (! $ticket) {
            abort(404);
        }

        /**
         * A guest ticket URL must contain a real token.
         */
        if ($token === null) {
            abort(404);
        }

        /**
         * Validate the raw 64-character token against the
         * SHA-256 hash stored on the ticket.
         */
        if (! $ticketService->verifyGuestTicketAccess(
            ticket: $ticket,
            token: $token,
        )) {
            abort(404);
        }

        /**
         * Token successfully authenticated the guest.
         *
         * Establish temporary browser access.
         */
        $ticketService->grantGuestTicketAccess(
            ticket: $ticket,
            minutes: 60,
        );

        $ticket->load([
            'messages.attachments',
        ]);

        return view('components.home.tickets.show', [
            'ticket' => $ticket,
            'messages' => $ticket->messages,
        ]);
    }
}
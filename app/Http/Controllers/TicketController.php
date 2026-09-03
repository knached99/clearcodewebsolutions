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
        string $ticketNumber,
        TicketService $ticketService
    ) {
        $ticket = $ticketService->findGuestTicket(
            ticketNumber: $ticketNumber,
        );

        if (! $ticket) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Require session access
        |--------------------------------------------------------------------------
        */

        $ticketService->requireGuestTicketAccess(
            ticket: $ticket,
        );

        $ticket->load('messages.attachments');

        return view('components.home.tickets.show', [
            'ticket' => $ticket,
            'messages' => $ticket->messages,
        ]);
    }
}

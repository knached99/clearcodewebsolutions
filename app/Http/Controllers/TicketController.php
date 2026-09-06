<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketService;

class TicketController extends Controller
{
    public function show()
    {
        return response('ADMIN SHOW');
    }

    public function guestShow(
        string $ticketNumber,
        string $token,
        TicketService $ticketService
    ) {
        /*
        |--------------------------------------------------------------------------
        | Find ticket by ticket number
        |--------------------------------------------------------------------------
        */

        $ticket = $ticketService->findGuestTicket(
            ticketNumber: $ticketNumber,
        );

        if (! $ticket) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate the token from the email URL
        |--------------------------------------------------------------------------
        |
        | The URL contains the RAW 64-character token.
        | TicketAccessToken stores SHA-256(token).
        |
        */

        if (! $ticketService->verifyGuestTicketAccess(
            ticket: $ticket,
            token: $token,
        )) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Store guest access in the session
        |--------------------------------------------------------------------------
        |
        | After the token has been successfully verified, the customer
        | can continue accessing the ticket through the guest session.
        |
        */

        session([
            'guest_ticket_id' => $ticket->id,
            'guest_ticket_token' => $token,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Load ticket
        |--------------------------------------------------------------------------
        */

        $ticket->load([
            'messages.attachments',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Guest ticket page
        |--------------------------------------------------------------------------
        */

        return view('components.home.tickets.show', [
            'ticket' => $ticket,
            'messages' => $ticket->messages,
        ]);
    }
}
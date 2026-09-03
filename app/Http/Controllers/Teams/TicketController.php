<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function show(string $current_team, string $ticket)
    {
        $ticket = Ticket::query()
            ->where('ticket_number', $ticket)
            ->firstOrFail();

        return view('teams.tickets.show', [
            'ticket' => $ticket,
        ]);
    }
}

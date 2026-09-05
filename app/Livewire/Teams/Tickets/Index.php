<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Team;
use App\Models\Ticket;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class Index extends Component
{
    public string $status = 'all';

    public string $priority = 'all';

    public string $search = '';

    public string $sort = 'latest';

    public int $teamId;

    public function mount(): void
    {
        $currentTeam = request()->route('current_team');

        if ($currentTeam instanceof Team) {
            $this->teamId = $currentTeam->id;

            return;
        }

        $team = Team::query()
            ->where('slug', $currentTeam)
            ->firstOrFail();

        $this->teamId = $team->id;
    }

    public function updatedSearch(): void
    {
        //
    }

    public function updatedStatus(): void
    {
        //
    }

    public function updatedPriority(): void
    {
        //
    }

    public function updatedSort(): void
    {
        //
    }

    public function moveTicket(int|string $ticketId, string $status): void
    {
        abort_unless(
            in_array(
                $status,
                ['open', 'pending', 'resolved', 'closed'],
                true
            ),
            422
        );

        $ticket = Ticket::query()
            ->whereKey($ticketId)
            ->where('team_id', $this->teamId)
            ->firstOrFail();

        $oldStatus = $ticket->status;

        // Don't send a notification if nothing actually changed.
        if ($oldStatus === $status) {
            return;
        }

        $ticket->update([
            'status' => $status,
            'last_activity_at' => now(),
        ]);

        // Notify the customer.
        if ($ticket->email) {
            Notification::route('mail', $ticket->email)
                ->notify(
                    new TicketStatusChangedNotification(
                        $ticket,
                        $oldStatus,
                        $status
                    )
                );
        }

        $this->dispatch(
            'ticket-moved',
            ticketId: $ticket->id,
            status: $status
        );
    }

    protected function team(): Team
    {
        return Team::query()
            ->whereKey($this->teamId)
            ->firstOrFail();
    }

    public function render()
    {
        $team = $this->team();

        $tickets = Ticket::query()
            ->where('team_id', $this->teamId)
            ->withCount('messages')
            ->when(
                $this->search !== '',
                function ($query) {
                    $search = '%' . $this->search . '%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('ticket_number', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('subject', 'like', $search);
                    });
                }
            )
            ->when(
                $this->status !== 'all',
                fn ($query) => $query->where(
                    'status',
                    $this->status
                )
            )
            ->when(
                $this->priority !== 'all',
                fn ($query) => $query->where(
                    'priority',
                    $this->priority
                )
            )
            ->when(
                $this->sort === 'oldest',
                fn ($query) => $query->oldest(
                    'last_activity_at'
                ),
                fn ($query) => $query->latest(
                    'last_activity_at'
                )
            )
            ->get();

        $counts = [
            'all' => Ticket::query()
                ->where('team_id', $this->teamId)
                ->count(),

            'open' => Ticket::query()
                ->where('team_id', $this->teamId)
                ->where('status', 'open')
                ->count(),

            'pending' => Ticket::query()
                ->where('team_id', $this->teamId)
                ->where('status', 'pending')
                ->count(),

            'resolved' => Ticket::query()
                ->where('team_id', $this->teamId)
                ->where('status', 'resolved')
                ->count(),

            'closed' => Ticket::query()
                ->where('team_id', $this->teamId)
                ->where('status', 'closed')
                ->count(),
        ];

        return view(
            'livewire.teams.tickets.index',
            [
                'tickets' => $tickets,
                'counts' => $counts,
                'team' => $team,
            ]
        );
    }
}
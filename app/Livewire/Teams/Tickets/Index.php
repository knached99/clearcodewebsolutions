<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Team;
use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';
    public string $priority = 'all';
    public string $search = '';
    public string $sort = 'latest';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function moveTicket(int|string $ticketId, string $status): void
    {
        abort_unless(
            in_array($status, ['open', 'pending', 'resolved', 'closed'], true),
            422
        );

        $team = $this->team();

        $ticket = Ticket::query()
            ->whereKey($ticketId)
            ->where('team_id', $team->id)
            ->firstOrFail();

        if ($ticket->status === $status) {
            return;
        }

        $ticket->update([
            'status' => $status,
            'last_activity_at' => now(),
        ]);

        $this->dispatch('ticket-moved', ticketId: $ticket->id, status: $status);
    }

    protected function team(): Team
    {
        $currentTeam = request()->route('current_team');

        if ($currentTeam instanceof Team) {
            return $currentTeam;
        }

        return Team::query()
            ->where('slug', $currentTeam)
            ->firstOrFail();
    }

    public function render()
    {
        $team = $this->team();

        $tickets = Ticket::query()
            ->where('team_id', $team->id)
            ->withCount('messages')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('ticket_number', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('subject', 'like', $search);
                });
            })
            ->when(
                $this->status !== 'all',
                fn ($query) => $query->where('status', $this->status)
            )
            ->when(
                $this->priority !== 'all',
                fn ($query) => $query->where('priority', $this->priority)
            )
            ->when(
                $this->sort === 'oldest',
                fn ($query) => $query->oldest('last_activity_at'),
                fn ($query) => $query->latest('last_activity_at')
            )
            ->paginate(50);

        $counts = [
            'all' => Ticket::where('team_id', $team->id)->count(),
            'open' => Ticket::where('team_id', $team->id)
                ->where('status', 'open')
                ->count(),
            'pending' => Ticket::where('team_id', $team->id)
                ->where('status', 'pending')
                ->count(),
            'resolved' => Ticket::where('team_id', $team->id)
                ->where('status', 'resolved')
                ->count(),
            'closed' => Ticket::where('team_id', $team->id)
                ->where('status', 'closed')
                ->count(),
        ];

        return view('livewire.teams.tickets.index', [
            'tickets' => $tickets,
            'counts' => $counts,
            'team' => $team,
        ]);
    }
}
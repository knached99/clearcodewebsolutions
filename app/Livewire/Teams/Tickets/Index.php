<?php

namespace App\Livewire\Teams\Tickets;

use App\Models\Team;
use App\Models\Ticket;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class Index extends Component
{
    public string $status = 'all';

    public string $priority = 'all';

    public string $search = '';

    public string $sort = 'latest';

    public int $teamId;

    private const STATUSES = [
        'open',
        'pending',
        'resolved',
        'closed',
    ];

    private const PRIORITIES = [
        'low',
        'normal',
        'high',
        'urgent',
    ];

    private const MAX_SEARCH_LENGTH = 100;

    public function mount(): void
    {
        $currentTeam = request()->route('current_team');

        $team = $currentTeam instanceof Team
            ? $currentTeam
            : Team::query()
                ->where('slug', $currentTeam)
                ->firstOrFail();

        $this->authorize(
            'view',
            $team,
        );

        $this->teamId = $team->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        $this->search = trim(
            mb_substr(
                $this->search,
                0,
                self::MAX_SEARCH_LENGTH,
            ),
        );
    }

    public function updatedStatus(): void
    {
        $this->validateFilters();
    }

    public function updatedPriority(): void
    {
        $this->validateFilters();
    }

    public function updatedSort(): void
    {
        $this->validateFilters();
    }

    protected function validateFilters(): void
    {
        $this->validate([
            'status' => [
                'required',
                'string',
                'in:all,' . implode(',', self::STATUSES),
            ],
            'priority' => [
                'required',
                'string',
                'in:all,' . implode(',', self::PRIORITIES),
            ],
            'sort' => [
                'required',
                'string',
                'in:latest,oldest',
            ],
            'search' => [
                'nullable',
                'string',
                'max:' . self::MAX_SEARCH_LENGTH,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Move ticket
    |--------------------------------------------------------------------------
    */

    public function moveTicket(
        int|string $ticketId,
        string $status,
    ): void {
        /*
         * Livewire method parameters are client-controlled.
         */
        $ticketId = filter_var(
            $ticketId,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ],
        );

        abort_unless(
            $ticketId !== false,
            422,
        );

        $status = strtolower(trim($status));

        abort_unless(
            in_array(
                $status,
                self::STATUSES,
                true,
            ),
            422,
        );

        /*
         * Get the ticket through the current team so a user cannot
         * move a ticket belonging to another team by changing the ID.
         */
        $ticket = Ticket::query()
            ->whereKey($ticketId)
            ->where('team_id', $this->teamId)
            ->firstOrFail();

        /*
         * Explicit authorization for the actual ticket operation.
         *
         * This assumes TicketPolicy::update() exists and verifies
         * that the authenticated user may modify this ticket.
         */
        $this->authorize(
            'update',
            $ticket,
        );

        $oldStatus = null;
        $changed = false;

        DB::transaction(function () use (
            $ticketId,
            $status,
            &$oldStatus,
            &$changed,
        ): void {
            /*
             * Lock the ticket so two simultaneous Livewire requests
             * cannot both make decisions based on the same old status.
             */
            $ticket = Ticket::query()
                ->whereKey($ticketId)
                ->where('team_id', $this->teamId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorize(
                'update',
                $ticket,
            );

            $oldStatus = (string) $ticket->status;

            /*
             * Nothing changed.
             */
            if ($oldStatus === $status) {
                return;
            }

            $ticket->update([
                'status' => $status,
                'last_activity_at' => now(),
            ]);

            $changed = true;

            /*
             * Keep the instance up-to-date for the notification.
             */
            $ticket->refresh();

            /*
             * Store the updated ticket temporarily on the component
             * only through the local property below.
             */
            $this->ticketForNotification = $ticket;
        });

        if (! $changed) {
            return;
        }

        /*
         * IMPORTANT:
         *
         * Email remains synchronous, but it happens AFTER the database
         * transaction has committed.
         *
         * Never hold a DB transaction open while waiting for SMTP.
         */
        $ticket = $this->ticketForNotification;

        if (
            $ticket instanceof Ticket
            && filled($ticket->email)
        ) {
            Notification::route(
                'mail',
                $ticket->email,
            )->notify(
                new TicketStatusChangedNotification(
                    $ticket,
                    $oldStatus,
                    $status,
                ),
            );
        }

        $this->dispatch(
            'ticket-moved',
            ticketId: $ticketId,
            status: $status,
        );
    }

    /**
     * Ticket used between the transaction and synchronous notification.
     */
    protected ?Ticket $ticketForNotification = null;

    /*
    |--------------------------------------------------------------------------
    | Team
    |--------------------------------------------------------------------------
    */

    protected function team(): Team
    {
        $team = Team::query()
            ->findOrFail($this->teamId);

        $this->authorize(
            'view',
            $team,
        );

        return $team;
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $this->validateFilters();

        $team = $this->team();

        $search = trim($this->search);

        $tickets = Ticket::query()
            ->where('team_id', $team->id)
            ->withCount('messages')
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    /*
                     * The % search is intentionally kept simple here.
                     *
                     * For a small team this is acceptable. If the ticket
                     * table becomes large, move this to full-text/search
                     * indexes rather than adding more LIKE queries.
                     */
                    $search = '%' . $search . '%';

                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where(
                                'ticket_number',
                                'like',
                                $search,
                            )
                            ->orWhere(
                                'name',
                                'like',
                                $search,
                            )
                            ->orWhere(
                                'email',
                                'like',
                                $search,
                            )
                            ->orWhere(
                                'subject',
                                'like',
                                $search,
                            );
                    });
                },
            )
            ->when(
                $this->status !== 'all',
                fn ($query) => $query->where(
                    'status',
                    $this->status,
                ),
            )
            ->when(
                $this->priority !== 'all',
                fn ($query) => $query->where(
                    'priority',
                    $this->priority,
                ),
            )
            ->orderBy(
                'last_activity_at',
                $this->sort === 'oldest'
                    ? 'asc'
                    : 'desc',
            )
            ->orderBy(
                'id',
                $this->sort === 'oldest'
                    ? 'asc'
                    : 'desc',
            )
            ->get();

        /*
         * One grouped query instead of five separate COUNT queries.
         */
        $statusCounts = Ticket::query()
            ->where('team_id', $team->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [
            'all' => (int) $statusCounts->sum(),

            'open' => (int) ($statusCounts['open'] ?? 0),

            'pending' => (int) ($statusCounts['pending'] ?? 0),

            'resolved' => (int) ($statusCounts['resolved'] ?? 0),

            'closed' => (int) ($statusCounts['closed'] ?? 0),
        ];

        return view(
            'livewire.teams.tickets.index',
            [
                'tickets' => $tickets,
                'counts' => $counts,
                'team' => $team,
            ],
        );
    }
}
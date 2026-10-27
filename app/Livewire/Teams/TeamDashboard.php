<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\TeamDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class TeamDashboard extends Component
{
    public Team $team;

    public string $filter = 'max';

    public ?string $customStartDate = null;

    public ?string $customEndDate = null;

    public array $dashboard = [];

    public function mount(string $current_team): void
    {
        Log::info('TeamDashboard: mount START', [
            'current_team' => $current_team,
        ]);

        try {
            $this->team = Team::query()
                ->where('slug', $current_team)
                ->firstOrFail();

            Log::info('TeamDashboard: team loaded', [
                'team_id' => $this->team->id,
                'team_slug' => $this->team->slug,
                'team_name' => $this->team->name,
                'filter' => $this->filter,
            ]);

            $this->loadDashboard();

            Log::info('TeamDashboard: mount COMPLETE', [
                'team_id' => $this->team->id,
                'filter' => $this->filter,
            ]);
        } catch (Throwable $e) {
            Log::error('TeamDashboard: mount FAILED', [
                'current_team' => $current_team,
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    */

    public function setFilter(string $filter): void
    {
        $filter = strtolower(trim($filter));

        if (! in_array(
            $filter,
            ['max', 'mtd', '3m', '6m', 'ytd', 'custom'],
            true
        )) {
            Log::warning('TeamDashboard: invalid filter', [
                'filter' => $filter,
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Custom range requires dates.
        |--------------------------------------------------------------------------
        */

        if ($filter === 'custom') {
            if (
                ! $this->customStartDate
                || ! $this->customEndDate
            ) {
                return;
            }

            if (
                ! $this->validCustomDateRange()
            ) {
                return;
            }
        }

        try {
            $this->filter = $filter;

            /*
            |--------------------------------------------------------------------------
            | If switching away from custom, retain the dates internally.
            |
            | This allows the user to switch back to Custom without
            | immediately losing their previous selection.
            |--------------------------------------------------------------------------
            */

            $this->loadDashboard();

            $this->dispatch(
                'dashboard-updated',
                data: $this->chartData()
            );
        } catch (Throwable $e) {
            Log::error('TeamDashboard: setFilter FAILED', [
                'team_id' => $this->team->id ?? null,
                'filter' => $filter,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Date Range
    |--------------------------------------------------------------------------
    */

    public function setCustomRange(
        string $startDate,
        string $endDate
    ): void {
        $validated = validator(
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ],
            [
                'startDate' => ['required', 'date_format:Y-m-d'],
                'endDate' => [
                    'required',
                    'date_format:Y-m-d',
                    'after_or_equal:startDate',
                ],
            ]
        )->validate();

        $this->customStartDate = $validated['startDate'];

        $this->customEndDate = $validated['endDate'];

        $this->filter = 'custom';

        $this->loadDashboard();

                $this->dispatch(
                    'dashboard-updated',
                    data: $this->chartData()
                );
        }

        


    protected function normalizeDate(
        ?string $date
    ): ?string {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    protected function validCustomDateRange(): bool
    {
        if (
            ! $this->customStartDate
            || ! $this->customEndDate
        ) {
            return false;
        }

        try {
            $start = Carbon::createFromFormat(
                'Y-m-d',
                $this->customStartDate
            )->startOfDay();

            $end = Carbon::createFromFormat(
                'Y-m-d',
                $this->customEndDate
            )->endOfDay();

            return $start->lte($end);
        } catch (Throwable) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    protected function loadDashboard(): void
    {
        $this->dashboard = app(
            TeamDashboardService::class
        )->dashboardData(
            team: $this->team,
            filter: $this->filter,
            customStartDate: $this->customStartDate,
            customEndDate: $this->customEndDate,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Chart Data
    |--------------------------------------------------------------------------
    */

    protected function chartData(): array
    {
        return [
            'revenue' => [
                'labels' => collect(
                    $this->dashboard['monthlyRevenue'] ?? []
                )
                    ->pluck('month')
                    ->values()
                    ->all(),

                'values' => collect(
                    $this->dashboard['monthlyRevenue'] ?? []
                )
                    ->pluck('value')
                    ->map(
                        fn ($value) => (float) $value
                    )
                    ->values()
                    ->all(),
            ],

            'customers' => [
                'labels' => collect(
                    $this->dashboard['customerRevenue'] ?? []
                )
                    ->pluck('name')
                    ->values()
                    ->all(),

                'values' => collect(
                    $this->dashboard['customerRevenue'] ?? []
                )
                    ->pluck('amount')
                    ->map(
                        fn ($value) => (float) $value
                    )
                    ->values()
                    ->all(),
            ],

            'donut' => [
                'subscription' => (float) (
                    $this->dashboard['subscriptionRevenue'] ?? 0
                ),

                'oneTime' => (float) (
                    $this->dashboard['oneTimeRevenue'] ?? 0
                ),
            ],
        ];
    }

    public function render(): View
    {
        return view(
            'livewire.teams.team-dashboard',
            [
                ...$this->dashboard,

                'chartData' => $this->chartData(),
            ]
        );
    }
}

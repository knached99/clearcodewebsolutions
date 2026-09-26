<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\TeamDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.app')]
class TeamDashboard extends Component
{
    public Team $team;

    public string $filter = 'max';

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

    public function setFilter(string $filter): void
    {
        Log::info('TeamDashboard: setFilter START', [
            'team_id' => $this->team->id ?? null,
            'team_slug' => $this->team->slug ?? null,
            'old_filter' => $this->filter,
            'requested_filter' => $filter,
        ]);

        $filter = strtolower(trim($filter));

        Log::debug('TeamDashboard: filter normalized', [
            'filter' => $filter,
        ]);

        if (! in_array($filter, ['max', 'mtd', 'ytd'], true)) {
            Log::warning('TeamDashboard: invalid filter', [
                'filter' => $filter,
            ]);

            return;
        }

        try {
            $this->filter = $filter;

            Log::info('TeamDashboard: filter assigned', [
                'team_id' => $this->team->id,
                'filter' => $this->filter,
            ]);

            $startedAt = microtime(true);

            $this->loadDashboard();


            $chartData = $this->chartData();

          
            $this->dispatch(
                'dashboard-updated',
                data: $chartData
            );
        
        } catch (Throwable $e) {
         
            throw $e;
        }
    }

    protected function loadDashboard(): void
    {
        $startedAt = microtime(true);

        try {
            $this->dashboard = app(TeamDashboardService::class)
                ->dashboardData(
                    $this->team,
                    $this->filter
                );
        
        } catch (Throwable $e) {
        
            throw $e;
        }
    }

    protected function chartData(): array
    {
        $data = [
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


        return $data;
    }

    public function render(): View
    {
        return view('livewire.teams.team-dashboard', [
            ...$this->dashboard,

            'chartData' => $this->chartData(),
        ]);
    }
}
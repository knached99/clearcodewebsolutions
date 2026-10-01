<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Services\TeamDashboardService;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TeamDashboardMap extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Team
    |--------------------------------------------------------------------------
    */

    public Team $team;

    /*
    |--------------------------------------------------------------------------
    | Reporting Filters
    |--------------------------------------------------------------------------
    |
    | These are reactive so the child map automatically follows the
    | parent TeamDashboard filter.
    |
    */

    #[Reactive]
    public string $filter = 'max';

    #[Reactive]
    public ?string $customStartDate = null;

    #[Reactive]
    public ?string $customEndDate = null;

    /*
    |--------------------------------------------------------------------------
    | Map Data
    |--------------------------------------------------------------------------
    */

    public array $mapData = [
        'type' => 'FeatureCollection',

        'features' => [],

        'meta' => [
            'payment_count' => 0,
            'customer_count' => 0,
            'mapped_revenue' => 0,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(
        Team $team,
    ): void {
        $this->team = $team;

        $this->loadMapData();
    }

    /*
    |--------------------------------------------------------------------------
    | Reactive Updates
    |--------------------------------------------------------------------------
    */

    public function updatedFilter(): void
    {
        /*
        |--------------------------------------------------------------------------
        | A custom range cannot be queried until dates exist.
        |--------------------------------------------------------------------------
        */

        if (
            $this->filter === 'custom'
            && (
                ! $this->customStartDate
                || ! $this->customEndDate
            )
        ) {
            return;
        }

        $this->loadMapData();
    }

    public function updatedCustomStartDate(): void
    {
        $this->refreshCustomRange();
    }

    public function updatedCustomEndDate(): void
    {
        $this->refreshCustomRange();
    }

    protected function refreshCustomRange(): void
    {
        if (
            $this->filter !== 'custom'
            || ! $this->customStartDate
            || ! $this->customEndDate
        ) {
            return;
        }

        $this->loadMapData();
    }

    /*
    |--------------------------------------------------------------------------
    | Load Map
    |--------------------------------------------------------------------------
    */

    protected function loadMapData(): void
    {
        $this->mapData =
            app(TeamDashboardService::class)
                ->paymentLocationMapData(
                    team: $this->team,
                    filter: $this->filter,
                    customStartDate: $this->customStartDate,
                    customEndDate: $this->customEndDate,
                );

        /*
        |--------------------------------------------------------------------------
        | Tell the browser to update Mapbox.
        |--------------------------------------------------------------------------
        */

        $this->dispatch(
            'dashboard-map-updated',
            data: $this->mapData,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.teams.team-dashboard-map'
        );
    }
}
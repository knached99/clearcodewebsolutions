<?php

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    /**
     * Get the currently selected team.
     */
    public function currentTeam(): ?array
    {
        $team = Auth::user()->currentTeam;

        if (! $team) {
            return null;
        }

        return [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
        ];
    }

    /**
     * Get all teams the authenticated user belongs to.
     *
     * @return Collection<int, Team>
     */
    public function teams(): Collection
    {
        return Auth::user()
            ->teams()
            ->orderBy('teams.id')
            ->get();
    }

    /**
     * Switch the authenticated user's current team.
     */
    public function switchTeam(string $slug): void
    {
        $user = Auth::user();

        $team = Team::query()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless(
            $user->belongsToTeam($team),
            403
        );

        $currentTeamSlug = $user->currentTeam?->slug;

        if (! $user->switchTeam($team)) {
            abort(403);
        }

        $referer = request()->header('Referer');

        /*
         * If there is no previous URL, go to the dashboard
         * with the selected team explicitly supplied.
         */
        if (! $referer) {
            $this->redirectRoute(
                'dashboard',
                [
                    'current_team' => $team->slug,
                ],
                navigate: true
            );

            return;
        }

        /*
         * If there wasn't a previously selected team,
         * we cannot replace a team segment in the URL.
         */
        if (! $currentTeamSlug) {
            $this->redirect(
                $referer,
                navigate: true
            );

            return;
        }

        /*
         * Replace either:
         *
         *   /old-team/...
         *
         * or:
         *
         *   ?current_team=old-team
         *
         * with the newly selected team.
         */
        $redirectTo = $this->replaceCurrentTeamInReferer(
            $referer,
            $currentTeamSlug,
            $team->slug
        );

        $this->redirect(
            $redirectTo ?? $referer,
            navigate: true
        );
    }

    /**
     * Replace the current team slug in a URL.
     */
    protected function replaceCurrentTeamInReferer(
        string $referer,
        string $currentTeamSlug,
        string $newTeamSlug
    ): ?string {
        /*
         * Handle team-prefixed URLs:
         *
         * /old-team/projects
         * /old-team/settings
         */
        $redirectTo = preg_replace(
            '#/' . preg_quote($currentTeamSlug, '#') . '(?=/|\?|$)#',
            '/' . $newTeamSlug,
            $referer,
            1
        );

        /*
         * Handle query-string URLs:
         *
         * ?current_team=old-team
         */
        return preg_replace(
            '#([?&]current_team=)' . preg_quote($currentTeamSlug, '#') . '(?=&|$)#',
            '$1' . $newTeamSlug,
            $redirectTo ?? $referer,
            1
        );
    }
};
?>


<div>
    <flux:dropdown position="bottom" align="start">
        <flux:button variant="ghost" class="group w-full justify-start in-data-flux-sidebar-collapsed-desktop:justify-center" data-test="team-switcher-trigger">
            <flux:icon name="users" class="hidden size-4 in-data-flux-sidebar-collapsed-desktop:block" />
            <span class="truncate font-semibold in-data-flux-sidebar-collapsed-desktop:hidden">{{ $this->currentTeam()['name'] ?? __('Select team') }}</span>
            <flux:icon
                name="chevrons-up-down"
                variant="micro"
                class="ms-auto size-4 in-data-flux-sidebar-collapsed-desktop:hidden"
            />
        </flux:button>

        <flux:menu class="min-w-56">
            <flux:menu.heading>{{ __('Teams') }}</flux:menu.heading>

            @foreach ($this->teams() as $team)
                <flux:menu.item
                    wire:click="switchTeam('{{ $team->slug }}')"
                    class="cursor-pointer"
                    data-test="team-switcher-item"
                >
                    <div class="flex w-full items-center justify-between">
                        <span>{{ $team->name }}</span>
                        @if ($team->isCurrent)
                            <flux:icon name="check" class="size-4" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach

            <flux:menu.separator />

            @if (Auth::user()->canCreateTeam())
            <flux:modal.trigger name="create-team-switcher">
                <flux:menu.item icon="plus" class="cursor-pointer" data-test="team-switcher-new-team">
                    {{ __('New team') }}
                </flux:menu.item>
            </flux:modal.trigger>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>

<?php

use App\Actions\Teams\CreateTeam;
use App\Data\UserTeam;
use App\Models\Team;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Teams')] class extends Component
{
    public string $name = '';

    public function createTeam(
        CreateTeam $createTeam
    ): void {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                new TeamName,
            ],
        ]);

        $team = $createTeam->handle(
            Auth::user(),
            $validated['name']
        );

        $this->dispatch(
            'close-modal',
            name: 'create-team'
        );

        $this->reset('name');

        Flux::toast(
            variant: 'success',
            text: __('Team created.')
        );

        $this->redirectRoute(
            'teams.edit',
            [
                'team' => $team->slug,
            ],
            navigate: true
        );
    }

    public function leaveTeam(
        int $teamId
    ): void {
        $team = Team::findOrFail($teamId);

        $user = Auth::user();

        Gate::authorize(
            'leave',
            $team
        );

        $fallbackTeam = $user->isCurrentTeam($team)
            ? $user->fallbackTeam($team)
            : null;

        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        $this->dispatch(
            'close-modal',
            name: "leave-team-{$teamId}"
        );

        Flux::toast(
            variant: 'success',
            text: __('You left the team ":name"', [
                'name' => $team->name,
            ])
        );

        $this->redirectRoute(
            'teams.index',
            navigate: true
        );
    }

    /**
     * @return Collection<int, UserTeam>
     */
    #[Computed]
    public function teams(): Collection
    {
        return Auth::user()->toUserTeams(
            includeCurrent: true
        );
    }
};
?>

<section class="w-full">

    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Teams') }}
    </flux:heading>

    <x-pages::settings.layout
        :heading="__('Teams')"
        :subheading="__('Manage your teams and team memberships.')"
    >

        {{-- ================================================================
            TEAM DIRECTORY
        ================================================================= --}}
        <div
            class="relative mt-8 overflow-hidden rounded-[1.75rem] border border-white/8 bg-[#09070f] shadow-2xl shadow-black/20"
        >

            {{-- Ambient glow --}}
            <div
                class="pointer-events-none absolute -left-32 -top-32 h-72 w-72 rounded-full bg-purple-600/12 blur-[110px]"
                aria-hidden="true"
            ></div>

            <div
                class="pointer-events-none absolute -right-32 -top-20 h-64 w-64 rounded-full bg-fuchsia-600/8 blur-[110px]"
                aria-hidden="true"
            ></div>


            {{-- ============================================================
                HEADER
            ============================================================= --}}
            <div class="relative flex flex-col gap-5 px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-8">

                <div class="flex items-center gap-4">

                    <div
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-purple-400/15 bg-linear-to-br from-purple-600/20 via-violet-500/10 to-fuchsia-500/10 shadow-lg shadow-purple-950/20"
                    >

                        <svg
                            class="h-5 w-5 text-purple-300/75"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M16 20v-1.5c0-2.5-1.8-4.5-4-4.5s-4 2-4 4.5V20"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />

                            <circle
                                cx="12"
                                cy="8"
                                r="3"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />

                            <path
                                d="M18 11a3 3 0 0 0-1.5-5.5M19 20v-1.5c0-1.7-.8-3.2-2-4"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>

                    </div>


                    <div>

                        <div class="flex items-center gap-2">

                            <h3 class="text-lg font-semibold tracking-tight text-white">
                                {{ __('Your teams') }}
                            </h3>

                            <span
                                class="rounded-full border border-white/7 bg-white/[0.025] px-2 py-0.5 text-[9px] font-medium text-white/30"
                            >
                                {{ $this->teams->count() }}
                            </span>

                        </div>

                        <p class="mt-0.5 text-xs text-white/25">
                            {{ __('Teams and memberships associated with your account.') }}
                        </p>

                    </div>

                </div>


                {{-- Create team --}}
                <flux:modal.trigger name="create-team">

                    <button
                        type="button"
                        x-data
                        x-on:click.prevent="$dispatch('open-modal', 'create-team')"
                        class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-4 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/40 active:translate-y-0"
                        data-test="teams-new-team-button"
                    >

                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 5v14M5 12h14"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>

                        {{ __('New team') }}

                    </button>

                </flux:modal.trigger>

            </div>


            {{-- ============================================================
                TEAM LIST
            ============================================================= --}}
            <div class="relative border-t border-white/7">

                @forelse ($this->teams as $team)

                    <div
                        class="group flex min-h-[78px] items-center justify-between gap-4 border-b border-white/5 px-6 py-4 last:border-b-0 sm:px-8"
                        data-test="team-row"
                    >

                        {{-- Team identity --}}
                        <div class="flex min-w-0 items-center gap-3.5">

                            {{-- Team avatar --}}
                            <div class="relative shrink-0">

                                @if ($team->isCurrent)

                                    <div
                                        class="absolute -inset-1 rounded-xl bg-purple-500/15 blur-md"
                                    ></div>

                                @endif

                                <div
                                    class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-white/8 bg-linear-to-br from-purple-600/20 via-violet-600/10 to-fuchsia-500/10 text-sm font-semibold text-purple-200/80"
                                >
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>

                            </div>


                            {{-- Team information --}}
                            <div class="min-w-0">

                                <div class="flex min-w-0 flex-wrap items-center gap-2">

                                    <span class="max-w-[60vw] truncate text-sm font-semibold text-white/75 sm:max-w-[420px]">
                                        {{ $team->name }}
                                    </span>


                                    @if ($team->isCurrent)

                                        <span
                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-purple-400/15 bg-purple-400/8 px-2 py-0.5 text-[8px] font-semibold uppercase tracking-[0.14em] text-purple-300/75"
                                        >

                                            <span class="h-1 w-1 rounded-full bg-purple-400 shadow-[0_0_7px_rgba(168,85,247,.8)]"></span>

                                            {{ __('Current') }}

                                        </span>

                                    @endif


                                    @if ($team->isPersonal)

                                        <span
                                            class="inline-flex shrink-0 rounded-full border border-white/7 bg-white/[0.025] px-2 py-0.5 text-[8px] font-semibold uppercase tracking-[0.14em] text-white/30"
                                        >
                                            {{ __('Personal') }}
                                        </span>

                                    @endif

                                </div>


                                <div class="mt-1 flex items-center gap-2">

                                    <span class="text-[11px] text-white/25">
                                        {{ $team->roleLabel }}
                                    </span>

                                    @if ($team->role === 'owner')

                                        <span class="h-0.5 w-0.5 rounded-full bg-white/15"></span>

                                        <span class="text-[10px] text-purple-300/35">
                                            {{ __('Full access') }}
                                        </span>

                                    @elseif ($team->role === 'admin')

                                        <span class="h-0.5 w-0.5 rounded-full bg-white/15"></span>

                                        <span class="text-[10px] text-white/15">
                                            {{ __('Management access') }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- Actions --}}
                        <div class="flex shrink-0 items-center gap-1">

                            @if (
                                ! $team->isPersonal &&
                                $team->role !== 'owner'
                            )

                                <flux:modal.trigger
                                    :name="'leave-team-'.$team->id"
                                >

                                    <flux:tooltip
                                        :content="__('Leave team')"
                                    >

                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="arrow-right-start-on-rectangle"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'leave-team-{{ $team->id }}')"
                                            class="cursor-pointer text-white/20! hover:bg-red-400/8! hover:text-red-300!"
                                            data-test="team-leave-button"
                                        />

                                    </flux:tooltip>

                                </flux:modal.trigger>

                            @endif


                            <flux:tooltip
                                :content="$team->role === 'member' ? __('View team') : __('Edit team')"
                            >

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    :icon="$team->role === 'member' ? 'eye' : 'pencil'"
                                    :href="route('teams.edit', $team->slug)"
                                    wire:navigate
                                    class="cursor-pointer text-white/25! hover:bg-purple-400/8! hover:text-purple-300!"
                                    :data-test="$team->role === 'member' ? 'team-view-button' : 'team-edit-button'"
                                />

                            </flux:tooltip>

                        </div>

                    </div>


                    {{-- ====================================================
                        LEAVE TEAM MODAL
                    ===================================================== --}}
                    @if (
                        ! $team->isPersonal &&
                        $team->role !== 'owner'
                    )

                        <flux:modal
                            :name="'leave-team-'.$team->id"
                            focusable
                            class="md:w-[440px]"
                        >

                            <div class="relative overflow-hidden">

                                <div
                                    class="pointer-events-none absolute -left-20 -top-20 h-40 w-40 rounded-full bg-red-500/8 blur-[70px]"
                                    aria-hidden="true"
                                ></div>


                                <form
                                    wire:submit="leaveTeam({{ $team->id }})"
                                    class="relative"
                                    novalidate
                                >

                                    {{-- Modal header --}}
                                    <div class="flex items-start gap-4">

                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-red-400/10 bg-red-500/8"
                                        >

                                            <svg
                                                class="h-5 w-5 text-red-300/65"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="M9 5H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                />

                                                <path
                                                    d="M14 8l4 4-4 4M18 12H8"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>

                                        </div>


                                        <div class="min-w-0">

                                            <flux:heading
                                                size="lg"
                                                class="tracking-tight"
                                            >
                                                {{ __('Leave team') }}
                                            </flux:heading>

                                            <p class="mt-1 text-sm leading-5 text-white/35">
                                                {{ __('Are you sure you want to leave :name?', [
                                                    'name' => $team->name,
                                                ]) }}
                                            </p>

                                        </div>

                                    </div>


                                    {{-- Warning --}}
                                    <div
                                        class="mt-5 rounded-xl border border-red-400/8 bg-red-500/[0.035] px-4 py-3"
                                    >

                                        <p class="text-[11px] leading-5 text-red-200/45">
                                            {{ __('You will lose access to this team and its resources. You can only regain access if a team administrator invites you again.') }}
                                        </p>

                                    </div>


                                    {{-- Actions --}}
                                    <div
                                        class="mt-6 flex items-center justify-end gap-2 border-t border-white/7 pt-5"
                                    >

                                        <flux:modal.close>

                                            <button
                                                type="button"
                                                class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-white/8 bg-white/[0.025] px-4 text-xs font-semibold text-white/45 transition hover:border-white/12 hover:bg-white/[0.05] hover:text-white/70 focus:outline-none focus:ring-2 focus:ring-purple-400/20"
                                            >
                                                {{ __('Cancel') }}
                                            </button>

                                        </flux:modal.close>


                                        <button
                                            type="submit"
                                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-red-400/15 bg-red-500/10 px-4 text-xs font-semibold text-red-300/80 transition hover:border-red-400/25 hover:bg-red-500/15 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-red-400/20"
                                            data-test="leave-team-confirm"
                                        >
                                            {{ __('Leave team') }}
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </flux:modal>

                    @endif

                @empty

                    {{-- Empty state --}}
                    <div class="px-6 py-14 text-center sm:px-8">

                        <div
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-purple-400/10 bg-purple-500/7"
                        >

                            <svg
                                class="h-5 w-5 text-purple-300/45"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M16 20v-1.5c0-2.5-1.8-4.5-4-4.5s-4 2-4 4.5V20"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                />

                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                            </svg>

                        </div>


                        <p class="mt-4 text-sm font-medium text-white/45">
                            {{ __('You don\'t belong to any teams yet.') }}
                        </p>

                        <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-white/20">
                            {{ __('Create a team to start collaborating with other people.') }}
                        </p>


                        <flux:modal.trigger name="create-team">

                            <button
                                type="button"
                                x-data
                                x-on:click.prevent="$dispatch('open-modal', 'create-team')"
                                class="mt-5 inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-lg border border-purple-300/15 bg-purple-500/8 px-4 text-xs font-semibold text-purple-200/75 transition hover:border-purple-300/25 hover:bg-purple-500/12 hover:text-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-400/30"
                            >

                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 5v14M5 12h14"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                {{ __('Create your first team') }}

                            </button>

                        </flux:modal.trigger>

                    </div>

                @endforelse

            </div>

        </div>


        {{-- ================================================================
            CREATE TEAM MODAL
        ================================================================= --}}
        <flux:modal
            name="create-team"
            :show="$errors->isNotEmpty()"
            focusable
            class="md:w-[440px]"
        >

            <div class="relative overflow-hidden">

                <div
                    class="pointer-events-none absolute -left-20 -top-20 h-40 w-40 rounded-full bg-purple-600/12 blur-[70px]"
                    aria-hidden="true"
                ></div>


                <form
                    wire:submit="createTeam"
                    class="relative"
                    novalidate
                >

                    {{-- Header --}}
                    <div class="flex items-start gap-4">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-purple-400/15 bg-purple-500/10 shadow-lg shadow-purple-950/20"
                        >

                            <svg
                                class="h-5 w-5 text-purple-300/75"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 5v14M5 12h14"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                />
                            </svg>

                        </div>


                        <div class="min-w-0">

                            <flux:heading
                                size="lg"
                                class="tracking-tight"
                            >
                                {{ __('Create a new team') }}
                            </flux:heading>

                            <p class="mt-1 text-sm leading-5 text-white/35">
                                {{ __('Give your team a name to get started.') }}
                            </p>

                        </div>

                    </div>


                    {{-- Input --}}
                    <div
                        class="mt-6 rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5 transition duration-200 focus-within:border-purple-400/25 focus-within:bg-white/[0.035]"
                    >

                        <flux:input
                            wire:model="name"
                            :label="__('Team name')"
                            :error="$errors->first('name')"
                            type="text"
                            autofocus
                            data-test="create-team-name"
                        />

                    </div>


                    {{-- Actions --}}
                    <div
                        class="mt-6 flex items-center justify-end gap-2 border-t border-white/7 pt-5"
                    >

                        <flux:modal.close>

                            <button
                                type="button"
                                class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-white/8 bg-white/[0.025] px-4 text-xs font-semibold text-white/45 transition hover:border-white/12 hover:bg-white/[0.05] hover:text-white/70 focus:outline-none focus:ring-2 focus:ring-purple-400/20"
                            >
                                {{ __('Cancel') }}
                            </button>

                        </flux:modal.close>


                        <button
                            type="submit"
                            class="group relative inline-flex h-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-5 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/40 active:translate-y-0"
                            data-test="create-team-submit"
                        >

                            <span
                                class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                            ></span>

                            <span class="relative flex items-center gap-2">

                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 5v14M5 12h14"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                {{ __('Create team') }}

                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </flux:modal>

    </x-pages::settings.layout>

</section>
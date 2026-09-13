<?php

use App\Data\TeamPermissions;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Rules\TeamName;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $teamModel;

    public string $teamName = '';

    public array $teamData = [];

    public array $members = [];

    public array $invitations = [];

    public array $availableRoles = [];

    public bool $isCurrentTeam = false;

    public function mount(Team $team): void
    {
        $this->teamModel = $team;

        $this->teamName = $team->name;

        $this->populateTeamData();
    }

    public function updateTeam(): void
    {
        Gate::authorize(
            'update',
            $this->teamModel
        );

        $validated = $this->validate([
            'teamName' => [
                'required',
                'string',
                'max:255',
                new TeamName,
            ],
        ]);

        $team = DB::transaction(function () use ($validated) {
            $team = Team::whereKey($this->teamModel->id)
                ->lockForUpdate()
                ->firstOrFail();

            $team->update([
                'name' => $validated['teamName'],
            ]);

            return $team;
        });

        $this->teamModel = $team;

        $this->populateTeamData();

        Flux::toast(
            variant: 'success',
            text: __('Team updated.')
        );

        $this->redirectRoute(
            'teams.edit',
            [
                'team' => $this->teamModel->fresh()->slug,
            ],
            navigate: true
        );
    }

    public function updateMember(
        int $userId,
        string $role
    ): void {
        Gate::authorize(
            'updateMember',
            $this->teamModel
        );

        $validated = Validator::make(
            ['role' => $role],
            [
                'role' => [
                    'required',
                    'string',
                    Rule::enum(TeamRole::class),
                ],
            ]
        )->validate();

        $this->teamModel
            ->memberships()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->update([
                'role' => TeamRole::from(
                    $validated['role']
                ),
            ]);

        $this->populateTeamData();

        Flux::toast(
            variant: 'success',
            text: __('Member role updated.')
        );
    }

    private function populateTeamData(): void
    {
        $user = Auth::user();

        $team = $this->teamModel->fresh();

        $this->teamData = [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'is_personal' => $team->is_personal,
        ];

        $this->members = $team
            ->members()
            ->get()
            ->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar ?? null,
                'initials' => $member->initials(),
                'role' => $member->pivot->role->value,
                'role_label' => $member->pivot->role->label(),
            ])
            ->toArray();

        $this->invitations = $team
            ->invitations()
            ->whereNull('accepted_at')
            ->get()
            ->map(fn ($invitation) => [
                'code' => $invitation->code,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'created_at' => $invitation->created_at->toISOString(),
            ])
            ->toArray();

        $this->availableRoles = TeamRole::assignable();

        $this->isCurrentTeam = $user->isCurrentTeam($team);
    }

    public function render()
    {
        $teamName = $this->teamData['name']
            ?? $this->teamModel->name;

        $title = $this->permissions->canUpdateTeam
            ? __('Edit :name', ['name' => $teamName])
            : __('View :name', ['name' => $teamName]);

        return $this->view()->title($title);
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions(
            $this->teamModel
        );
    }
};
?>

<section class="w-full">

    @include('partials.settings-heading')
    @push('styles')
        <style>
        [data-test="team-name-input"] {
        width: 100% !important;
        min-width: 0 !important;
    }

    [data-test="team-name-input"] input {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    </style>
    @endpush
    <flux:heading level="2" class="sr-only">
        {{ __('Teams') }}
    </flux:heading>

    <x-pages::settings.layout
        :heading="__('Teams')"
        :subheading="__('Manage your team, members, and invitations.')"
    >

        {{-- ================================================================
            TEAM SURFACE
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
                class="pointer-events-none absolute -right-32 -top-24 h-64 w-64 rounded-full bg-fuchsia-600/8 blur-[110px]"
                aria-hidden="true"
            ></div>


            {{-- ============================================================
                TEAM HEADER
            ============================================================= --}}
            <div class="relative px-6 py-6 sm:px-8">

                <div class="space-y-6">

                    {{-- Team identity --}}
                    <div class="flex min-w-0 items-start gap-4">

                        <div class="relative shrink-0">

                            <div
                                class="absolute -inset-1 rounded-2xl bg-linear-to-br from-purple-500/30 via-violet-500/10 to-fuchsia-500/20 blur-lg"
                            ></div>

                            <div
                                class="relative flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-linear-to-br from-purple-600 via-violet-600 to-fuchsia-600 text-lg font-semibold text-white shadow-xl shadow-purple-950/40"
                            >
                                {{ strtoupper(substr($teamData['name'] ?? 'T', 0, 1)) }}
                            </div>

                        </div>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                {{-- Don't truncate the team name --}}
                                <h3
                                    class="min-w-0 break-words text-xl font-semibold leading-tight tracking-tight text-white"
                                >
                                    {{ $teamData['name'] }}
                                </h3>

                                @if ($isCurrentTeam)

                                    <span
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-purple-400/15 bg-purple-400/8 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-purple-300/75"
                                    >
                                        <span class="h-1 w-1 rounded-full bg-purple-400"></span>

                                        {{ __('Current') }}
                                    </span>

                                @endif

                                @if ($teamData['is_personal'])

                                    <span
                                        class="inline-flex shrink-0 items-center rounded-full border border-white/8 bg-white/[0.025] px-2 py-0.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-white/30"
                                    >
                                        {{ __('Personal') }}
                                    </span>

                                @endif

                            </div>

                            <p class="mt-1 break-all text-xs text-white/25">
                                {{ $teamData['slug'] }}
                            </p>

                        </div>

                    </div>


                    {{-- Team name editor --}}
                    @if ($this->permissions->canUpdateTeam)

                        <form
                            wire:submit="updateTeam"
                            class="rounded-2xl border border-white/7 bg-white/[0.025] p-4 shadow-inner shadow-black/10"
                            novalidate
                        >

                            <div class="flex flex-col gap-3 lg:flex-row lg:items-end">

                                <div class="min-w-0 flex-1">

                                    <flux:input
                                        wire:model="teamName"
                                        :label="__('Team name')"
                                        :error="$errors->first('teamName')"
                                        data-test="team-name-input"
                                        class="w-full"
                                    />

                                </div>

                                <button
                                    type="submit"
                                    class="inline-flex h-10 w-full shrink-0 cursor-pointer items-center justify-center gap-2 rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-5 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/40 active:translate-y-0 lg:w-auto"
                                    data-test="team-save-button"
                                >

                                    <svg
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M5 12.5 9.5 17 19 7"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    {{ __('Save') }}

                                </button>

                            </div>

                        </form>

                    @endif

                </div>

            </div>



            {{-- ============================================================
                MEMBERS
            ============================================================= --}}
            <div class="relative border-t border-white/7">

                <div class="flex items-center justify-between gap-4 px-6 py-5 sm:px-8">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-purple-400/10 bg-purple-500/8"
                        >

                            <svg
                                class="h-4 w-4 text-purple-300/65"
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

                                <h4 class="text-sm font-semibold text-white/80">
                                    {{ __('Team members') }}
                                </h4>

                                <span
                                    class="rounded-full border border-white/7 bg-white/[0.025] px-2 py-0.5 text-[9px] font-medium text-white/30"
                                >
                                    {{ count($members) }}
                                </span>

                            </div>

                            @if (
                                $this->permissions->canAddMember ||
                                $this->permissions->canUpdateMember ||
                                $this->permissions->canRemoveMember
                            )

                                <p class="mt-0.5 text-[11px] text-white/20">
                                    {{ __('People with access to this team') }}
                                </p>

                            @endif

                        </div>

                    </div>


                    @if ($this->permissions->canCreateInvitation)

                        <flux:modal.trigger name="invite-member">

                            <button
                                type="button"
                                class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 rounded-lg border border-purple-300/15 bg-purple-500/8 px-3.5 text-xs font-semibold text-purple-200/80 transition hover:border-purple-300/25 hover:bg-purple-500/12 hover:text-purple-100 focus:outline-none focus:ring-2 focus:ring-purple-400/30"
                                data-test="invite-member-button"
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

                                {{ __('Invite member') }}

                            </button>

                        </flux:modal.trigger>

                    @endif

                </div>


                {{-- Member list --}}
                <div class="border-t border-white/5">

                    @forelse ($members as $member)

                        <div
                            class="group flex min-h-[72px] items-center justify-between gap-4 border-b border-white/5 px-6 py-3.5 last:border-b-0 sm:px-8"
                            data-test="member-row"
                        >

                            {{-- Member --}}
                            <div class="flex min-w-0 items-center gap-3.5">

                                <div class="shrink-0">

                                    <flux:avatar
                                        :name="$member['name']"
                                        :initials="$member['initials']"
                                        size="sm"
                                    />

                                </div>

                                <div class="min-w-0">

                                    <div class="flex min-w-0 items-center gap-2">

                                        <span class="truncate text-sm font-medium text-white/75">
                                            {{ $member['name'] }}
                                        </span>

                                        @if ($member['role'] === 'owner')

                                            <span
                                                class="hidden shrink-0 rounded-full border border-purple-400/10 bg-purple-400/6 px-1.5 py-0.5 text-[8px] font-semibold uppercase tracking-[0.12em] text-purple-300/60 sm:inline-flex"
                                            >
                                                {{ __('Owner') }}
                                            </span>

                                        @endif

                                    </div>

                                    <p class="max-w-[45vw] truncate text-[11px] text-white/25 sm:max-w-none">
                                        {{ $member['email'] }}
                                    </p>

                                </div>

                            </div>


                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">

                                @if (
                                    $member['role'] !== 'owner' &&
                                    $this->permissions->canUpdateMember
                                )

                                    <flux:dropdown
                                        position="bottom"
                                        align="end"
                                    >

                                        <flux:button
                                            variant="outline"
                                            size="sm"
                                            icon:trailing="chevron-down"
                                            class="cursor-pointer border-white/8! bg-white/[0.025]! text-white/50! hover:bg-white/[0.05]!"
                                            data-test="member-role-trigger"
                                        >
                                            {{ $member['role_label'] }}
                                        </flux:button>

                                        <flux:menu>

                                            @foreach ($availableRoles as $role)

                                                <flux:menu.item
                                                    as="button"
                                                    type="button"
                                                    wire:click="updateMember({{ $member['id'] }}, '{{ $role['value'] }}')"
                                                    class="cursor-pointer"
                                                    data-test="member-role-option"
                                                >
                                                    {{ $role['label'] }}
                                                </flux:menu.item>

                                            @endforeach

                                        </flux:menu>

                                    </flux:dropdown>

                                @else

                                    <span
                                        class="inline-flex items-center rounded-lg border border-white/7 bg-white/[0.025] px-2.5 py-1.5 text-[10px] font-medium text-white/35"
                                    >
                                        {{ $member['role_label'] }}
                                    </span>

                                @endif


                                @if (
                                    $member['role'] !== 'owner' &&
                                    $this->permissions->canRemoveMember
                                )

                                    <flux:modal.trigger name="remove-member-{{ $member['id'] }}">

                                        <flux:tooltip :content="__('Remove member')">

                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="x-mark"
                                                class="cursor-pointer text-white/20! hover:bg-red-400/8! hover:text-red-300!"
                                                data-test="member-remove-button"
                                            />

                                        </flux:tooltip>

                                    </flux:modal.trigger>

                                @endif

                            </div>

                        </div>


                        @if (
                            $member['role'] !== 'owner' &&
                            $this->permissions->canRemoveMember
                        )

                            <livewire:pages::teams.remove-member-modal
                                :team="$teamModel"
                                :member-id="$member['id']"
                                :member-name="$member['name']"
                                :modal-name="'remove-member-'.$member['id']"
                                :key="'remove-member-modal-'.$member['id']"
                            />

                        @endif

                    @empty

                        <div class="px-6 py-10 text-center sm:px-8">

                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl border border-white/7 bg-white/[0.025]">

                                <svg
                                    class="h-4 w-4 text-white/20"
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

                            <p class="mt-3 text-xs font-medium text-white/35">
                                {{ __('No team members yet.') }}
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- ============================================================
                PENDING INVITATIONS
            ============================================================= --}}
            @if (count($invitations) > 0)

                <div class="relative border-t border-white/7">

                    <div class="flex items-center gap-3 px-6 py-5 sm:px-8">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-amber-300/10 bg-amber-400/7"
                        >

                            <svg
                                class="h-4 w-4 text-amber-300/65"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <path
                                    d="m3 7 9 6 9-6"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                        </div>

                        <div>

                            <div class="flex items-center gap-2">

                                <h4 class="text-sm font-semibold text-white/75">
                                    {{ __('Pending invitations') }}
                                </h4>

                                <span
                                    class="rounded-full border border-amber-300/10 bg-amber-300/6 px-2 py-0.5 text-[9px] font-medium text-amber-200/50"
                                >
                                    {{ count($invitations) }}
                                </span>

                            </div>

                            <p class="mt-0.5 text-[11px] text-white/20">
                                {{ __('Waiting for these people to join your team') }}
                            </p>

                        </div>

                    </div>


                    <div class="border-t border-white/5">

                        @foreach ($invitations as $invitation)

                            <div
                                class="flex min-h-[68px] items-center justify-between gap-4 border-b border-white/5 px-6 py-3.5 last:border-b-0 sm:px-8"
                                data-test="invitation-row"
                            >

                                <div class="flex min-w-0 items-center gap-3.5">

                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/7 bg-white/[0.025]"
                                    >

                                        <svg
                                            class="h-4 w-4 text-white/25"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <rect
                                                x="3"
                                                y="5"
                                                width="18"
                                                height="14"
                                                rx="2"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                            />
                                            <path
                                                d="m3 7 9 6 9-6"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="truncate text-sm font-medium text-white/60">
                                            {{ $invitation['email'] }}
                                        </p>

                                        <p class="mt-0.5 text-[11px] text-white/20">
                                            {{ $invitation['role_label'] }}
                                        </p>

                                    </div>

                                </div>


                                @if ($this->permissions->canCancelInvitation)

                                    <flux:modal.trigger
                                        name="cancel-invitation-{{ $invitation['code'] }}"
                                    >

                                        <flux:tooltip :content="__('Cancel invitation')">

                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                icon="x-mark"
                                                class="cursor-pointer text-white/20! hover:bg-red-400/8! hover:text-red-300!"
                                                data-test="invitation-cancel-button"
                                            />

                                        </flux:tooltip>

                                    </flux:modal.trigger>

                                @endif

                            </div>


                            @if ($this->permissions->canCancelInvitation)

                                <livewire:pages::teams.cancel-invitation-modal
                                    :team="$teamModel"
                                    :invitation-code="$invitation['code']"
                                    :invitation-email="$invitation['email']"
                                    :modal-name="'cancel-invitation-'.$invitation['code']"
                                    :key="'cancel-invitation-modal-'.$invitation['code']"
                                />

                            @endif

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- ============================================================
                DELETE TEAM
            ============================================================= --}}
            @if (
                $this->permissions->canDeleteTeam &&
                ! $teamData['is_personal']
            )

                <div
                    class="relative border-t border-red-400/8 bg-red-500/[0.012]"
                >

                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">

                        <div class="flex min-w-0 items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-400/10 bg-red-500/7"
                            >

                                <svg
                                    class="h-4 w-4 text-red-300/60"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                            </div>

                            <div class="min-w-0">

                                <p class="text-xs font-semibold text-white/60">
                                    {{ __('Delete team') }}
                                </p>

                                <p class="mt-0.5 text-[11px] text-white/20">
                                    {{ __('Permanently delete this team and its data. This cannot be undone.') }}
                                </p>

                            </div>

                        </div>


                        <flux:modal.trigger name="delete-team">

                            <button
                                type="button"
                                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-red-400/15 bg-red-500/7 px-4 text-xs font-semibold text-red-300/75 transition hover:border-red-400/25 hover:bg-red-500/12 hover:text-red-200 focus:outline-none focus:ring-2 focus:ring-red-400/20"
                                data-test="delete-team-button"
                            >
                                {{ __('Delete team') }}
                            </button>

                        </flux:modal.trigger>

                    </div>

                </div>

            @endif

        </div>


        {{-- ================================================================
            MODALS
        ================================================================= --}}
        @if ($this->permissions->canCreateInvitation)

            <livewire:pages::teams.invite-member-modal
                :team="$teamModel"
            />

        @endif

        @if (
            $this->permissions->canDeleteTeam &&
            ! $teamData['is_personal']
        )

            <livewire:pages::teams.delete-team-modal
                :team="$teamModel"
            />

        @endif

    </x-pages::settings.layout>

</section>
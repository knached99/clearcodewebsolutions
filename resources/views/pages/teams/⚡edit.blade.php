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
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
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
        Gate::authorize('update', $this->teamModel);

        $validated = $this->validate([
            'teamName' => ['required', 'string', 'max:255', new TeamName()],
        ]);

        $team = DB::transaction(function () use ($validated) {
            $team = Team::whereKey($this->teamModel->id)->lockForUpdate()->firstOrFail();

            $team->update([
                'name' => $validated['teamName'],
            ]);

            return $team;
        });

        $this->teamModel = $team;

        $this->populateTeamData();

        Flux::toast(variant: 'success', text: __('Team updated.'));

        $this->redirectRoute(
            'teams.edit',
            [
                'team' => $this->teamModel->fresh()->slug,
            ],
            navigate: true,
        );
    }

    public function updateMember(int $userId, string $role): void
    {
        Gate::authorize('updateMember', $this->teamModel);

        $validated = Validator::make(
            ['role' => $role],
            [
                'role' => ['required', 'string', Rule::enum(TeamRole::class)],
            ],
        )->validate();

        $this->teamModel
            ->memberships()
            ->where('user_id', $userId)
            ->firstOrFail()
            ->update([
                'role' => TeamRole::from($validated['role']),
            ]);

        $this->populateTeamData();

        Flux::toast(variant: 'success', text: __('Member role updated.'));
    }

    #[On('team-member-removed')]
    public function refreshMembers(): void
    {
        $this->populateTeamData();
    }

    #[On('team-member-invited')]
    public function refreshInvitations(): void
    {
        $this->populateTeamData();
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
            ->map(
                fn($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->avatar ?? null,
                    'initials' => $member->initials(),
                    'role' => $member->pivot->role->value,
                    'role_label' => $member->pivot->role->label(),
                ],
            )
            ->toArray();

        $this->invitations = $team
            ->invitations()
            ->whereNull('accepted_at')
            ->get()
            ->map(
                fn($invitation) => [
                    'code' => $invitation->code,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'created_at' => $invitation->created_at->toISOString(),
                ],
            )
            ->toArray();

        /*
         * TeamRole::assignable() returns arrays:
         *
         * [
         *     [
         *         'value' => 'member',
         *         'label' => 'Member',
         *     ],
         *     ...
         * ]
         */
        $this->availableRoles = TeamRole::assignable();

        $this->isCurrentTeam = $user->isCurrentTeam($team);
    }

    #[Computed]
    public function permissions(): TeamPermissions
    {
        return Auth::user()->toTeamPermissions($this->teamModel);
    }

    public function render()
    {
        $teamName = $this->teamData['name'] ?? $this->teamModel->name;

        $title = $this->permissions->canUpdateTeam ? __('Edit :name', ['name' => $teamName]) : __('View :name', ['name' => $teamName]);

        return $this->view()->title($title);
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

    <x-pages::settings.layout :heading="__('Teams')" :subheading="__('Manage your team, members, and invitations.')">

        {{-- ================================================================
            TEAM SURFACE
        ================================================================= --}}

        <div
            class="relative mt-8 overflow-hidden rounded-[1.75rem]
            border border-zinc-200 bg-white shadow-xl shadow-zinc-200/50
            dark:border-purple-500/10 dark:bg-black dark:shadow-2xl
            dark:shadow-purple-950/20">

            {{-- Ambient purple glow --}}
            <div class="pointer-events-none absolute -left-40 -top-40 h-80 w-80
                rounded-full bg-purple-500/[0.04] blur-[120px]
                dark:bg-purple-700/10"
                aria-hidden="true"></div>

            <div class="pointer-events-none absolute -right-40 -top-32 h-72 w-72
                rounded-full bg-fuchsia-500/[0.035] blur-[120px]
                dark:bg-fuchsia-700/8"
                aria-hidden="true"></div>


            {{-- ============================================================
                TEAM HEADER
            ================================================================= --}}

            <div class="relative px-6 py-7 sm:px-8">

                <div class="space-y-6">

                    {{-- Team identity --}}
                    <div class="flex min-w-0 items-start gap-4">

                        <div class="relative shrink-0">

                            <div
                                class="absolute -inset-1 rounded-2xl
                                bg-linear-to-br from-purple-500/20
                                via-violet-500/10 to-fuchsia-500/15
                                blur-lg
                                dark:from-purple-500/30
                                dark:via-violet-500/15
                                dark:to-fuchsia-500/20">
                            </div>

                            <div
                                class="relative flex h-14 w-14 items-center
                                justify-center rounded-2xl border
                                border-purple-300/30
                                bg-linear-to-br from-purple-600
                                via-violet-600 to-fuchsia-600
                                text-lg font-semibold text-white
                                shadow-lg shadow-purple-500/20
                                dark:border-purple-400/20
                                dark:shadow-xl dark:shadow-purple-950/50">
                                {{ strtoupper(substr($teamData['name'] ?? 'T', 0, 1)) }}
                            </div>

                        </div>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3
                                    class="min-w-0 break-words text-xl
                                    font-semibold leading-tight tracking-tight
                                    text-zinc-900
                                    dark:text-white">
                                    {{ $teamData['name'] }}
                                </h3>

                                @if ($isCurrentTeam)
                                    <span
                                        class="inline-flex shrink-0 items-center
                                        gap-1.5 rounded-full border
                                        border-purple-200
                                        bg-purple-50 px-2 py-0.5
                                        text-[9px] font-semibold uppercase
                                        tracking-[0.14em] text-purple-700
                                        dark:border-purple-400/20
                                        dark:bg-purple-500/10
                                        dark:text-purple-300">
                                        <span
                                            class="h-1 w-1 rounded-full
                                            bg-purple-500 dark:bg-purple-400"></span>

                                        {{ __('Current') }}
                                    </span>
                                @endif

                                @if ($teamData['is_personal'])
                                    <span
                                        class="inline-flex shrink-0 items-center
                                        rounded-full border border-zinc-200
                                        bg-zinc-100 px-2 py-0.5
                                        text-[9px] font-semibold uppercase
                                        tracking-[0.14em] text-zinc-600
                                        dark:border-zinc-800
                                        dark:bg-zinc-900
                                        dark:text-zinc-400">
                                        {{ __('Personal') }}
                                    </span>
                                @endif

                            </div>

                            <p
                                class="mt-1 break-all text-xs
                                text-zinc-500
                                dark:text-zinc-600">
                                {{ $teamData['slug'] }}
                            </p>

                        </div>

                    </div>


                    {{-- ====================================================
                        TEAM NAME EDITOR
                    ===================================================== --}}

                    @if ($this->permissions->canUpdateTeam)
                        <form wire:submit="updateTeam"
                            class="rounded-2xl border
                            border-zinc-200 bg-zinc-50 p-4
                            shadow-inner shadow-zinc-200/30
                            dark:border-purple-500/10
                            dark:bg-zinc-950/80
                            dark:shadow-inner dark:shadow-black/30"
                            novalidate>

                            <div class="flex flex-col gap-3
                                lg:flex-row lg:items-end">

                                <div class="min-w-0 flex-1">

                                    <flux:input wire:model="teamName" :label="__('Team name')"
                                        :error="$errors->first('teamName')" data-test="team-name-input"
                                        class="w-full" />

                                </div>

                                <button type="submit" wire:loading.attr="disabled" wire:target="updateTeam"
                                    class="inline-flex h-10 w-full
                                    shrink-0 cursor-pointer items-center
                                    justify-center gap-2 rounded-lg
                                    border border-purple-400/20
                                    bg-linear-to-r from-purple-700
                                    via-violet-600 to-fuchsia-600
                                    px-5 text-xs font-semibold text-white
                                    shadow-lg shadow-purple-500/20
                                    transition duration-300
                                    hover:-translate-y-0.5
                                    hover:shadow-purple-500/30
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-purple-400/40
                                    active:translate-y-0
                                    disabled:cursor-not-allowed
                                    disabled:opacity-60
                                    dark:shadow-purple-950/40
                                    dark:hover:shadow-purple-900/40
                                    lg:w-auto"
                                    data-test="team-save-button">

                                    <span wire:loading.remove wire:target="updateTeam" class="flex items-center gap-2">

                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M5 12.5 9.5 17 19 7" stroke="currentColor" stroke-width="1.8"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                        {{ __('Save') }}

                                    </span>

                                    <span wire:loading wire:target="updateTeam" class="flex items-center gap-2">

                                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none"
                                            aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" stroke="currentColor"
                                                stroke-width="2" class="opacity-25" />

                                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" />
                                        </svg>

                                        {{ __('Saving...') }}

                                    </span>

                                </button>

                            </div>

                        </form>
                    @endif

                </div>

            </div>


            {{-- ============================================================
                MEMBERS
            ================================================================= --}}

            <div class="relative border-t border-zinc-200
                dark:border-purple-500/10">

                <div class="flex items-center justify-between gap-4
                    px-6 py-5 sm:px-8">

                    <div class="flex items-center gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center
                            justify-center rounded-xl border
                            border-purple-200 bg-purple-50
                            dark:border-purple-500/15
                            dark:bg-purple-500/10">
                            <flux:icon name="users"
                                class="size-4 text-purple-600
                                dark:text-purple-300" />
                        </div>

                        <div>

                            <div class="flex items-center gap-2">

                                <h4
                                    class="text-sm font-semibold
                                    text-zinc-900 dark:text-white">
                                    {{ __('Team members') }}
                                </h4>

                                <span
                                    class="rounded-full border
                                    border-purple-200 bg-purple-50
                                    px-2 py-0.5 text-[9px] font-medium
                                    text-purple-700
                                    dark:border-purple-500/15
                                    dark:bg-purple-500/10
                                    dark:text-purple-300">
                                    {{ count($members) }}
                                </span>

                            </div>

                            @if ($this->permissions->canAddMember || $this->permissions->canUpdateMember || $this->permissions->canRemoveMember)
                                <p
                                    class="mt-0.5 text-[11px]
                                    text-zinc-500 dark:text-zinc-600">
                                    {{ __('People with access to this team') }}
                                </p>
                            @endif

                        </div>

                    </div>


                    {{-- Invite member --}}
                    @if ($this->permissions->canCreateInvitation)
                        <flux:modal.trigger name="invite-member">

                            <button type="button"
                                class="inline-flex h-9 cursor-pointer
                                items-center justify-center gap-2
                                rounded-lg border border-purple-200
                                bg-purple-50 px-3.5 text-xs
                                font-semibold text-purple-700
                                transition hover:border-purple-300
                                hover:bg-purple-100 hover:text-purple-800
                                focus:outline-none
                                focus:ring-2
                                focus:ring-purple-400/30
                                dark:border-purple-400/20
                                dark:bg-purple-600/10
                                dark:text-purple-200
                                dark:hover:border-purple-400/30
                                dark:hover:bg-purple-600/20
                                dark:hover:text-white"
                                data-test="invite-member-button">

                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.7"
                                        stroke-linecap="round" />
                                </svg>

                                {{ __('Invite member') }}

                            </button>

                        </flux:modal.trigger>
                    @endif

                </div>


                {{-- Member list --}}
                <div class="border-t border-zinc-100
                    dark:border-purple-500/5">

                    @forelse ($members as $member)

                        <div class="group flex min-h-[72px] items-center
                            justify-between gap-4 border-b
                            border-zinc-100 px-6 py-3.5
                            transition hover:bg-purple-50/50
                            last:border-b-0
                            dark:border-purple-500/5
                            dark:hover:bg-purple-500/[0.025]
                            sm:px-8"
                            data-test="member-row">

                            {{-- Member --}}
                            <div class="flex min-w-0 items-center gap-3.5">

                                <div class="shrink-0">

                                    <flux:avatar :name="$member['name']" :initials="$member['initials']"
                                        size="sm" />

                                </div>

                                <div class="min-w-0">

                                    <div class="flex min-w-0 items-center gap-2">

                                        <span
                                            class="truncate text-sm
                                            font-medium text-zinc-900
                                            dark:text-white">
                                            {{ $member['name'] }}
                                        </span>

                                        @if ($member['role'] === 'owner')
                                            <span
                                                class="hidden shrink-0
                                                rounded-full border
                                                border-purple-200
                                                bg-purple-50 px-1.5 py-0.5
                                                text-[8px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-purple-700
                                                dark:border-purple-400/15
                                                dark:bg-purple-500/10
                                                dark:text-purple-300
                                                sm:inline-flex">
                                                {{ __('Owner') }}
                                            </span>
                                        @endif

                                    </div>

                                    <p
                                        class="max-w-[45vw] truncate
                                        text-[11px] text-zinc-500
                                        dark:text-zinc-600
                                        sm:max-w-none">
                                        {{ $member['email'] }}
                                    </p>

                                </div>

                            </div>


                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">

                                {{-- Role --}}
                                @if ($member['role'] !== 'owner' && $this->permissions->canUpdateMember)
                                    <flux:dropdown position="bottom" align="end">

                                        <flux:button variant="outline" size="sm" icon:trailing="chevron-down"
                                            class="cursor-pointer
                                            border-zinc-200!
                                            bg-white!
                                            text-zinc-600!
                                            hover:border-purple-300!
                                            hover:bg-purple-50!
                                            hover:text-purple-700!
                                            dark:border-zinc-800!
                                            dark:bg-zinc-950!
                                            dark:text-zinc-400!
                                            dark:hover:border-purple-500/30!
                                            dark:hover:bg-purple-500/10!
                                            dark:hover:text-purple-200!"
                                            data-test="member-role-trigger">
                                            {{ $member['role_label'] }}
                                        </flux:button>

                                        <flux:menu>

                                            @foreach ($availableRoles as $role)
                                                <flux:menu.item as="button" type="button"
                                                    wire:click="updateMember({{ $member['id'] }}, '{{ $role['value'] }}')"
                                                    class="cursor-pointer" data-test="member-role-option">
                                                    {{ $role['label'] }}
                                                </flux:menu.item>
                                            @endforeach

                                        </flux:menu>

                                    </flux:dropdown>
                                @else
                                    @if ($member['role'] !== 'owner')
                                        <span
                                            class="inline-flex items-center
                                            rounded-lg border
                                            border-zinc-200 bg-zinc-50
                                            px-2.5 py-1.5 text-[10px]
                                            font-medium text-zinc-500
                                            dark:border-zinc-800
                                            dark:bg-zinc-950
                                            dark:text-zinc-500">
                                            {{ $member['role_label'] }}
                                        </span>
                                    @endif
                                @endif


                                {{-- Remove member --}}
                                @if ($member['role'] !== 'owner' && $this->permissions->canRemoveMember)
                                    <flux:modal.trigger name="remove-member-{{ $member['id'] }}">

                                        <flux:tooltip :content="__('Remove member')">

                                            <flux:button variant="ghost" size="sm" icon="x-mark"
                                                class="cursor-pointer
                                                text-zinc-400!
                                                hover:bg-red-50!
                                                hover:text-red-500!
                                                dark:text-zinc-600!
                                                dark:hover:bg-red-500/10!
                                                dark:hover:text-red-400!"
                                                data-test="member-remove-button" />

                                        </flux:tooltip>

                                    </flux:modal.trigger>
                                @endif

                            </div>

                        </div>


                        {{-- Remove member modal --}}
                        @if ($member['role'] !== 'owner' && $this->permissions->canRemoveMember)
                            <livewire:pages::teams.remove-member-modal :team="$teamModel" :member-id="$member['id']"
                                :member-name="$member['name']" :modal-name="'remove-member-' . $member['id']" :key="'remove-member-modal-' . $member['id']" />
                        @endif

                    @empty

                        <div class="px-6 py-12 text-center sm:px-8">

                            <div
                                class="mx-auto flex h-11 w-11
                                items-center justify-center rounded-xl
                                border border-purple-200
                                bg-purple-50
                                dark:border-purple-500/15
                                dark:bg-purple-500/10">
                                <flux:icon name="users"
                                    class="size-5 text-purple-600
                                    dark:text-purple-300" />
                            </div>

                            <p
                                class="mt-4 text-sm font-medium
                                text-zinc-900 dark:text-white">
                                {{ __('No team members yet.') }}
                            </p>

                            <p class="mt-1 text-xs text-zinc-500
                                dark:text-zinc-600">
                                {{ __('Invite someone to get started.') }}
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- ============================================================
                PENDING INVITATIONS
            ================================================================= --}}

            @if (count($invitations) > 0)

                <div class="relative border-t border-zinc-200
                    dark:border-purple-500/10">

                    <div class="flex items-center gap-3 px-6 py-5
                        sm:px-8">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center
                            justify-center rounded-xl border
                            border-purple-200 bg-purple-50
                            dark:border-purple-500/15
                            dark:bg-purple-500/10">
                            <flux:icon name="envelope"
                                class="size-4 text-purple-600
                                dark:text-purple-300" />
                        </div>

                        <div>

                            <div class="flex items-center gap-2">

                                <h4
                                    class="text-sm font-semibold
                                    text-zinc-900 dark:text-white">
                                    {{ __('Pending invitations') }}
                                </h4>

                                <span
                                    class="rounded-full border
                                    border-purple-200 bg-purple-50
                                    px-2 py-0.5 text-[9px] font-medium
                                    text-purple-700
                                    dark:border-purple-500/15
                                    dark:bg-purple-500/10
                                    dark:text-purple-300">
                                    {{ count($invitations) }}
                                </span>

                            </div>

                            <p
                                class="mt-0.5 text-[11px]
                                text-zinc-500 dark:text-zinc-600">
                                {{ __('Waiting for these people to join your team') }}
                            </p>

                        </div>

                    </div>


                    <div class="border-t border-zinc-100
                        dark:border-purple-500/5">

                        @foreach ($invitations as $invitation)
                            <div class="flex min-h-[68px] items-center
                                justify-between gap-4 border-b
                                border-zinc-100 px-6 py-3.5
                                last:border-b-0
                                dark:border-purple-500/5
                                sm:px-8"
                                data-test="invitation-row">

                                <div class="flex min-w-0 items-center
                                    gap-3.5">

                                    <div
                                        class="flex h-9 w-9 shrink-0
                                        items-center justify-center
                                        rounded-xl border
                                        border-zinc-200 bg-zinc-50
                                        dark:border-zinc-800
                                        dark:bg-zinc-950">

                                        <flux:icon name="envelope"
                                            class="size-4 text-zinc-400
                                            dark:text-zinc-600" />

                                    </div>

                                    <div class="min-w-0">

                                        <p
                                            class="truncate text-sm
                                            font-medium text-zinc-800
                                            dark:text-zinc-300">
                                            {{ $invitation['email'] }}
                                        </p>

                                        <div
                                            class="mt-0.5 flex items-center
                                            gap-2 text-[11px]
                                            text-zinc-500
                                            dark:text-zinc-600">

                                            <span>
                                                {{ $invitation['role_label'] }}
                                            </span>

                                            <span
                                                class="text-zinc-300
                                                dark:text-zinc-800">
                                                •
                                            </span>

                                            <span>
                                                {{ \Carbon\Carbon::parse($invitation['created_at'])->diffForHumans() }}
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                @if ($this->permissions->canCancelInvitation)
                                    <flux:modal.trigger name="cancel-invitation-{{ $invitation['code'] }}">

                                        <flux:tooltip :content="__('Cancel invitation')">

                                            <flux:button variant="ghost" size="sm" icon="x-mark"
                                                class="cursor-pointer
                                                text-zinc-400!
                                                hover:bg-red-50!
                                                hover:text-red-500!
                                                dark:text-zinc-600!
                                                dark:hover:bg-red-500/10!
                                                dark:hover:text-red-400!"
                                                data-test="invitation-cancel-button" />

                                        </flux:tooltip>

                                    </flux:modal.trigger>
                                @endif

                            </div>


                            @if ($this->permissions->canCancelInvitation)
                                <livewire:pages::teams.cancel-invitation-modal :team="$teamModel" :invitation-code="$invitation['code']"
                                    :invitation-email="$invitation['email']" :modal-name="'cancel-invitation-' . $invitation['code']" :key="'cancel-invitation-modal-' . $invitation['code']" />
                            @endif
                        @endforeach

                    </div>

                </div>

            @endif


            {{-- ============================================================
                DELETE TEAM
            ================================================================= --}}

            @if ($this->permissions->canDeleteTeam && !$teamData['is_personal'])
                <div
                    class="relative border-t border-red-200
                    bg-red-50/40
                    dark:border-red-500/10
                    dark:bg-red-500/[0.015]">

                    <div
                        class="flex flex-col gap-4 px-6 py-6
                        sm:flex-row sm:items-center
                        sm:justify-between sm:px-8">

                        <div class="flex min-w-0 items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center
                                justify-center rounded-lg border
                                border-red-200 bg-red-50
                                dark:border-red-500/15
                                dark:bg-red-500/10">

                                <flux:icon name="trash"
                                    class="size-4 text-red-500
                                    dark:text-red-400" />

                            </div>

                            <div class="min-w-0">

                                <p
                                    class="text-xs font-semibold
                                    text-zinc-800 dark:text-zinc-300">
                                    {{ __('Delete team') }}
                                </p>

                                <p
                                    class="mt-0.5 text-[11px]
                                    text-zinc-500 dark:text-zinc-600">
                                    {{ __('Permanently delete this team and its data. This cannot be undone.') }}
                                </p>

                            </div>

                        </div>


                        <flux:modal.trigger name="delete-team">

                            <button type="button"
                                class="inline-flex h-9 shrink-0
                                cursor-pointer items-center
                                justify-center rounded-lg border
                                border-red-200 bg-red-50
                                px-4 text-xs font-semibold
                                text-red-600 transition
                                hover:border-red-300
                                hover:bg-red-100
                                hover:text-red-700
                                focus:outline-none
                                focus:ring-2
                                focus:ring-red-500/20
                                dark:border-red-500/20
                                dark:bg-red-500/10
                                dark:text-red-400
                                dark:hover:border-red-500/30
                                dark:hover:bg-red-500/15
                                dark:hover:text-red-300"
                                data-test="delete-team-button">
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
            <livewire:pages::teams.invite-member-modal :team="$teamModel" :key="'invite-member-modal-' . $teamModel->id" />
        @endif


        @if ($this->permissions->canDeleteTeam && !$teamData['is_personal'])
            <livewire:pages::teams.delete-team-modal :team="$teamModel" :key="'delete-team-modal-' . $teamModel->id" />
        @endif

    </x-pages::settings.layout>

</section>

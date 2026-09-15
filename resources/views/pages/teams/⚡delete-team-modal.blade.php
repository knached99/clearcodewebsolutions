<?php

use App\Data\UserTeam;
use App\Models\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Team $team;

    public string $deleteName = '';

    public function mount(
        Team $team
    ): void {
        $this->team = $team;
    }

    #[Computed]
    public function deleteConfirmLabel(): string
    {
        return __('Type ":name" to confirm', [
            'name' => $this->team->name,
        ]);
    }

    public function deleteTeam(): void
    {
        Gate::authorize(
            'delete',
            $this->team
        );

        $validated = $this->validate([
            'deleteName' => [
                'required',
                'string',
            ],
        ]);

        if (
            $validated['deleteName'] !==
            $this->team->name
        ) {
            $this->addError(
                'deleteName',
                __('The team name does not match.')
            );

            return;
        }

        $user = Auth::user();

        $fallbackTeam = $user->isCurrentTeam($this->team)
            ? $user->fallbackTeam($this->team)
            : null;

        DB::transaction(function () use ($user): void {
            User::where(
                'current_team_id',
                $this->team->id
            )
                ->where(
                    'id',
                    '!=',
                    $user->id
                )
                ->each(
                    fn (User $affectedUser) =>
                        $affectedUser->switchTeam(
                            $affectedUser->personalTeam()
                        )
                );

            $this->team
                ->invitations()
                ->delete();

            $this->team
                ->memberships()
                ->delete();

            $this->team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Flux::toast(
            variant: 'success',
            text: __('Team deleted.')
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
    public function otherTeams(): Collection
    {
        return Auth::user()->teams();
    }
};
?>

<flux:modal
    name="delete-team"
    :show="$errors->isNotEmpty()"
    focusable
    class="md:w-[460px]"
>
    <div class="relative overflow-hidden">

        {{-- Ambient destructive glow --}}
        <div
            class="pointer-events-none absolute -left-24 -top-24 h-48 w-48 rounded-full bg-red-500/5 blur-[90px] dark:bg-red-500/8"
            aria-hidden="true"
        ></div>

        <div
            class="pointer-events-none absolute -bottom-24 -right-24 h-40 w-40 rounded-full bg-purple-600/5 blur-[80px] dark:bg-purple-600/7"
            aria-hidden="true"
        ></div>

        <form
            wire:submit="deleteTeam"
            class="relative"
            novalidate
        >

            {{-- ============================================================
                HEADER
            ============================================================= --}}
            <div class="flex items-start gap-4">

                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-red-200 bg-red-50 dark:border-red-400/10 dark:bg-red-500/8"
                >
                    <svg
                        class="h-5 w-5 text-red-600 dark:text-red-300/65"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M4 7h16"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />

                        <path
                            d="M10 11v6M14 11v6"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />

                        <path
                            d="M6 7l1 13h10l1-13"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linejoin="round"
                        />

                        <path
                            d="M9 7V4h6v3"
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
                        class="tracking-tight text-gray-950! dark:text-white!"
                    >
                        {{ __('Delete team') }}
                    </flux:heading>

                    <p class="mt-1 text-sm leading-5 text-gray-600 dark:text-white/35">
                        {{ __('Permanently remove this team and everything associated with it.') }}
                    </p>

                </div>

            </div>


            {{-- ============================================================
                WARNING
            ============================================================= --}}
            <div
                class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 dark:border-red-400/10 dark:bg-red-500/[0.035]"
            >
                <div class="flex gap-3">

                    <div
                        class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-100 dark:border-red-400/10 dark:bg-red-500/7"
                    >
                        <svg
                            class="h-3.5 w-3.5 text-red-600 dark:text-red-300/65"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 3 2.8 19h18.4L12 3Z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M12 9v4"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                            />

                            <circle
                                cx="12"
                                cy="16.5"
                                r=".8"
                                fill="currentColor"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-red-700 dark:text-red-200/65">
                            {{ __('This action cannot be undone.') }}
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-gray-600 dark:text-white/30">
                            {{ __('Deleting this team will permanently remove its memberships and pending invitations.') }}
                        </p>

                    </div>

                </div>
            </div>


            {{-- ============================================================
                CONFIRMATION
            ============================================================= --}}
            <div class="mt-5">

                <div class="mb-2.5 flex items-center justify-between gap-3">

                    <div class="min-w-0">

                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-white/30">
                            {{ __('Confirm deletion') }}
                        </p>

                        <p class="mt-1 truncate text-[11px] text-gray-500 dark:text-white/20">
                            {{ __('Enter the team name exactly as shown.') }}
                        </p>

                    </div>

                    <span
                        class="hidden shrink-0 rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-[8px] font-semibold uppercase tracking-[0.14em] text-red-600 sm:inline-flex dark:border-red-400/10 dark:bg-red-500/5 dark:text-red-300/45"
                    >
                        {{ __('Permanent') }}
                    </span>

                </div>


                <div
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 transition duration-200 focus-within:border-red-300 focus-within:bg-white dark:border-white/8 dark:bg-white/[0.025] dark:focus-within:border-red-400/20 dark:focus-within:bg-white/[0.035]"
                >

                    <flux:input
                        wire:model="deleteName"
                        :label="$this->deleteConfirmLabel"
                        :error="$errors->first('deleteName')"
                        type="text"
                        autocomplete="off"
                        autofocus
                        data-test="delete-team-name"
                    />

                </div>

            </div>


            {{-- ============================================================
                ACTIONS
            ============================================================= --}}
            <div
                class="mt-6 flex items-center justify-end gap-2 border-t border-gray-200 pt-5 dark:border-white/7"
            >

                <flux:modal.close>

                    <button
                        type="button"
                        class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-gray-200 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-400/20 dark:border-white/8 dark:bg-white/[0.025] dark:text-white/45 dark:shadow-none dark:hover:border-white/12 dark:hover:bg-white/[0.05] dark:hover:text-white/70"
                    >
                        {{ __('Cancel') }}
                    </button>

                </flux:modal.close>


                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 text-xs font-semibold text-red-700 shadow-sm transition duration-200 hover:border-red-300 hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-400/20 active:translate-y-px disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-400/15 dark:bg-red-500/10 dark:text-red-300/80 dark:shadow-lg dark:shadow-red-950/10 dark:hover:border-red-400/25 dark:hover:bg-red-500/15 dark:hover:text-red-200"
                    data-test="delete-team-confirm"
                >

                    <span
                        wire:loading.remove
                        class="flex items-center gap-2"
                    >

                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 7h16"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                            />

                            <path
                                d="M10 11v6M14 11v6"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                            />

                            <path
                                d="M6 7l1 13h10l1-13M9 7V4h6v3"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        {{ __('Delete team') }}

                    </span>


                    <span
                        wire:loading
                        class="flex items-center gap-2"
                    >

                        <svg
                            class="h-3.5 w-3.5 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="2"
                                class="opacity-25"
                            />

                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>

                        {{ __('Deleting...') }}

                    </span>

                </button>

            </div>

        </form>

    </div>
</flux:modal>

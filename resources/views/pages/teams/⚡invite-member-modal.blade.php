<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use App\Rules\UniqueTeamInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Team $team;

    public bool $show = false;

    #[Validate]
    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    /**
     * Open the invitation modal.
     */
    public function open(): void
    {
        $this->authorize(
            'inviteMember',
            $this->team
        );

        $this->resetValidation();

        $this->inviteEmail = '';

        $this->inviteRole = TeamRole::Member->value;

        $this->show = true;
    }

    /**
     * Close the invitation modal.
     */
    public function close(): void
    {
        $this->resetValidation();

        $this->inviteEmail = '';

        $this->inviteRole = TeamRole::Member->value;

        $this->show = false;
    }

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'inviteEmail' => [
                'required',
                'string',
                'email',
                'max:255',
                new UniqueTeamInvitation($this->team),
            ],
            'inviteRole' => [
                'required',
                'string',
                'in:' . implode(',', [
                    TeamRole::Member->value,
                    TeamRole::Admin->value,
                ]),
            ],
        ];
    }

    /**
     * Send the team invitation.
     */
    public function invite(): void
    {
        /**
         * Authorize using TeamPolicy::inviteMember().
         *
         * This checks TeamPermission::CreateInvitation rather
         * than requiring the user's pivot role to literally be
         * "admin".
         */
        $this->authorize(
            'inviteMember',
            $this->team
        );

        $this->validate();

        $email = strtolower(
            trim($this->inviteEmail)
        );

        /**
         * Generate a strong temporary password.
         *
         * It is never stored in plaintext in the database.
         * Only the queued notification receives it.
         */
        $temporaryPassword = Str::password(
            length: 20,
            letters: true,
            numbers: true,
            symbols: true,
        );

        $invitation = DB::transaction(
            function () use (
                $email,
                $temporaryPassword
            ): TeamInvitation {
                $inviter = auth()->user();

                abort_unless(
                    $inviter,
                    403
                );

                /**
                 * Find an existing account by normalized email.
                 */
                $user = User::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [$email]
                    )
                    ->first();

                /**
                 * Create the account if it does not exist.
                 */
                if (! $user) {
                    $user = User::create([
                        'name' => Str::before(
                            $email,
                            '@'
                        ),
                        'email' => $email,
                        'password' => Hash::make(
                            $temporaryPassword
                        ),
                        'email_verified_at' => now(),
                    ]);

                    /**
                     * Force the invited account to change its password.
                     */
                    $user->forceFill([
                        'must_change_password' => true,
                    ])->save();
                } else {
                    /**
                     * Existing users also receive a temporary password
                     * and must change it before continuing.
                     *
                     * We deliberately do NOT overwrite their existing
                     * email verification state.
                     */
                    $user->forceFill([
                        'password' => Hash::make(
                            $temporaryPassword
                        ),
                        'must_change_password' => true,
                    ])->save();
                }

                /**
                 * Safety check:
                 *
                 * UniqueTeamInvitation normally prevents an existing
                 * member from reaching this point, but enforce it again
                 * server-side.
                 */
                abort_if(
                    $this->team
                        ->members()
                        ->whereKey($user->id)
                        ->exists(),
                    422,
                    __('This user is already a member of the team.')
                );

                /**
                 * Create the invitation.
                 */
                return TeamInvitation::create([
                    'team_id' => $this->team->id,
                    'email' => $email,
                    'role' => $this->inviteRole,
                    'invited_by' => $inviter->id,
                    'expires_at' => now()->addDays(3),
                ]);
            }
        );

        /**
         * Queue the invitation email.
         *
         * The TeamInvitation notification implements ShouldQueue
         * and uses afterCommit(), so the email is processed by the
         * queue after the database transaction has committed.
         */
        Notification::route(
            'mail',
            $email
        )->notify(
            new TeamInvitationNotification(
                $invitation->id,
                $temporaryPassword,
            )
        );

        $this->resetValidation();

        $this->inviteEmail = '';

        $this->inviteRole = TeamRole::Member->value;

        $this->show = false;

        session()->flash(
            'success',
            __('Invitation sent successfully.')
        );

        $this->dispatch(
            'team-member-invited'
        );
    }
};
?>

<div>

    <flux:modal
        wire:model="show"
        name="invite-member"
        class="md:w-[480px]"
    >

        {{-- ================================================================
            HEADER
        ================================================================= --}}
        <div class="relative -mx-1 overflow-hidden rounded-2xl">

            {{-- Ambient glow --}}
            <div
                class="pointer-events-none absolute -left-20 -top-20 h-40 w-40 rounded-full bg-purple-600/15 blur-[70px]"
                aria-hidden="true"
            ></div>

            <div
                class="relative flex items-start gap-4"
            >

                {{-- Icon --}}
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-purple-400/15 bg-purple-500/10 shadow-lg shadow-purple-950/20"
                >

                    <svg
                        class="h-5 w-5 text-purple-300/80"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="M16 21v-1.5c0-2.5-1.8-4.5-4-4.5s-4 2-4 4.5V21"
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
                            d="M19 8v5M16.5 10.5h5"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>

                </div>


                <div class="min-w-0">

                    <div class="flex items-center gap-2">

                        <flux:heading
                            size="lg"
                            class="tracking-tight"
                        >
                            {{ __('Invite team member') }}
                        </flux:heading>

                    </div>

                    <p class="mt-1 text-sm leading-5 text-white/35">
                        {{ __('Invite someone to join :team.', [
                            'team' => $team->name,
                        ]) }}
                    </p>

                </div>

            </div>

        </div>


        {{-- ================================================================
            FORM
        ================================================================= --}}
        <form
            wire:submit="invite"
            class="mt-6"
            novalidate
        >

            <div class="space-y-4">

                {{-- Email --}}
                <div
                    class="rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5 transition duration-200 focus-within:border-purple-400/25 focus-within:bg-white/[0.035]"
                >

                    <flux:input
                        wire:model="inviteEmail"
                        :label="__('Email address')"
                        :error="$errors->first('inviteEmail')"
                        type="email"
                        placeholder="name@example.com"
                        autocomplete="email"
                        autofocus
                    />

                </div>


                {{-- Role --}}
                <div
                    class="rounded-xl border border-white/8 bg-white/[0.025] px-4 py-3.5 transition duration-200 focus-within:border-purple-400/25 focus-within:bg-white/[0.035]"
                >

                    <flux:select
                        wire:model="inviteRole"
                        :label="__('Role')"
                        :error="$errors->first('inviteRole')"
                    >

                        <flux:select.option value="member">
                            {{ __('Member') }}
                        </flux:select.option>

                        <flux:select.option value="admin">
                            {{ __('Admin') }}
                        </flux:select.option>

                    </flux:select>

                </div>


                {{-- Invitation information --}}
                <div
                    class="flex gap-3 rounded-xl border border-purple-400/8 bg-purple-500/[0.035] px-4 py-3"
                >

                    <div
                        class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-purple-400/10 bg-purple-500/8"
                    >

                        <svg
                            class="h-3.5 w-3.5 text-purple-300/60"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 16v-4"
                                stroke="currentColor"
                                stroke-width="1.6"
                                stroke-linecap="round"
                            />

                            <circle
                                cx="12"
                                cy="8"
                                r=".8"
                                fill="currentColor"
                            />

                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                        </svg>

                    </div>

                    <p class="text-[11px] leading-5 text-white/30">
                        {{ __('The invited user will receive a temporary password by email and will be required to create a new password before continuing.') }}
                    </p>

                </div>

            </div>


            {{-- ============================================================
                ACTIONS
            ============================================================= --}}
            <div
                class="mt-6 flex items-center justify-end gap-2 border-t border-white/7 pt-5"
            >

                <button
                    type="button"
                    wire:click="close"
                    class="inline-flex h-10 cursor-pointer items-center justify-center rounded-lg border border-white/8 bg-white/[0.025] px-4 text-xs font-semibold text-white/45 transition hover:border-white/12 hover:bg-white/[0.05] hover:text-white/70 focus:outline-none focus:ring-2 focus:ring-purple-400/20"
                >
                    {{ __('Cancel') }}
                </button>


                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group relative inline-flex h-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-5 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/40 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <span
                        class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                    ></span>

                    <span
                        wire:loading.remove
                        class="relative flex items-center gap-2"
                    >

                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 12h15"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />

                            <path
                                d="m13 6 6 6-6 6"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        {{ __('Send invitation') }}

                    </span>


                    <span
                        wire:loading
                        class="relative flex items-center gap-2"
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

                        {{ __('Sending...') }}

                    </span>

                </button>

            </div>

        </form>

    </flux:modal>

</div>
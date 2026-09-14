<?php

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\Teams\TeamInvitation as TeamInvitationNotification;
use App\Rules\UniqueTeamInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Team $team;

    #[Validate]
    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    private const INVITE_MAX_ATTEMPTS = 10;

    private const INVITE_DECAY_SECONDS = 900;

    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    /**
     * Validation rules.
     */
    protected function rules(): array
    {
        return [
            'inviteEmail' => [
                'bail',
                'required',
                'string',
                'lowercase',
                'email:rfc,dns,spoof',
                'max:254',
                new UniqueTeamInvitation($this->team),
            ],

            'inviteRole' => [
                'bail',
                'required',
                'string',
                Rule::in([
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
        $this->successMessage = null; 
        $this->errorMessage = null; 
        /*
         * Always authorize at the point of action.
         */
        Gate::authorize(
            'inviteMember',
            $this->team,
        );

        /*
         * Normalize before validation.
         */
        $this->inviteEmail = strtolower(
            trim($this->inviteEmail),
        );

        $this->validate();

        /*
         * Prevent repeated invitation attempts.
         */
        $this->ensureRateLimit();

        $inviter = auth()->user();

        abort_unless(
            $inviter,
            403,
        );

        $email = $this->inviteEmail;
        $role = $this->inviteRole;

        /*
         * Only populated when a brand-new user account is created.
         */
        $temporaryPassword = null;

        try {
            $invitation = DB::transaction(
                function () use (
                    $email,
                    $role,
                    $inviter,
                    &$temporaryPassword,
                ): TeamInvitation {
                    /*
                     * Lock the team row so concurrent invitation
                     * requests for the same team are serialized.
                     */
                    $team = Team::query()
                        ->lockForUpdate()
                        ->findOrFail($this->team->id);

                    /*
                     * Re-authorize using the current team record.
                     */
                    Gate::authorize(
                        'inviteMember',
                        $team,
                    );

                    /*
                     * Find an existing account first.
                     */
                    $existingUser = User::query()
                        ->where('email', $email)
                        ->first();

                    /*
                     * Existing users keep their existing password.
                     */
                    if ($existingUser) {
                        if (
                            $team->members()
                                ->whereKey($existingUser->id)
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'inviteEmail' => __(
                                    'This user is already a member of the team.'
                                ),
                            ]);
                        }
                    } else {
                        /*
                         * Create a new account only when necessary.
                         */
                        $temporaryPassword = Str::password(
                            length: 20,
                            letters: true,
                            numbers: true,
                            symbols: true,
                        );

                        $existingUser = User::create([
                            'name' => Str::before(
                                $email,
                                '@',
                            ),

                            'email' => $email,

                            'password' => Hash::make(
                                $temporaryPassword,
                            ),

                            'email_verified_at' => now(),

                            'must_change_password' => true,
                        ]);
                    }

                    /*
                     * Re-check membership immediately before creating
                     * the invitation.
                     */
                    if (
                        $team->members()
                            ->whereKey($existingUser->id)
                            ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'inviteEmail' => __(
                                'This user is already a member of the team.'
                            ),
                        ]);
                    }

                    /*
                     * Prevent duplicate active invitations.
                     */
                    $existingInvitation = TeamInvitation::query()
                        ->where('team_id', $team->id)
                        ->where('email', $email)
                        ->whereNull('accepted_at')
                        ->where(
                            'expires_at',
                            '>',
                            now(),
                        )
                        ->exists();

                    if ($existingInvitation) {
                        throw ValidationException::withMessages([
                            'inviteEmail' => __(
                                'An active invitation already exists for this email address.'
                            ),
                        ]);
                    }

                    /*
                     * Create the invitation.
                     */
                    return TeamInvitation::create([
                        'team_id' => $team->id,
                        'email' => $email,
                        'role' => $role,
                        'invited_by' => $inviter->id,
                        'expires_at' => now()->addDays(3),
                    ]);
                },
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

           $this->errorMessage = __(
             'The invitation could not be created. Please try again.'
            );

            return;
        }

        /*
         * Rate-limit only after the invitation was successfully created.
         */
        $this->hitRateLimit();

        /*
         * Send the notification after the database transaction has
         * successfully committed.
         */
        try {
            Notification::route(
                'mail',
                $email,
            )->notify(
                new TeamInvitationNotification(
                    $invitation->id,
                    $temporaryPassword,
                ),
            );
        } catch (\Throwable $exception) {
            /*
             * The invitation already exists at this point.
             *
             * Log the notification failure rather than telling the user
             * that the invitation itself failed.
             */
            report($exception);

            $this->resetValidation();

            $this->inviteEmail = '';
            $this->inviteRole = TeamRole::Member->value;

            $this->errorMessage = __(
                'The invitation was created, but the email could not be sent. Please try again later.'
            );
            logger()->info('Team invitation password', [
            'email' => $email,
            'invitation_id' => $invitation->id,
            'temporary_password' => $temporaryPassword,
        ]);


            $this->dispatch(
                'team-member-invited',
            );

            return;
        }

        /*
         * Reset the form.
         */
        $this->resetValidation();

        $this->inviteEmail = '';
        $this->inviteRole = TeamRole::Member->value;

        /*
         * Close the Flux modal.
         */
        $this->dispatch(
            'close-modal',
            name: 'invite-member',
        );

        $this->successMessage = __('Invitation sent successfully.');

        /*
         * Let the parent refresh its team/member data.
         */
        $this->dispatch(
            'team-member-invited',
        );
    }

    /**
     * Ensure invitation attempts are rate-limited.
     */
    protected function ensureRateLimit(): void
    {
        $ip = request()->ip() ?? 'unknown';

        $emailKey = 'team-invite:email:' . hash(
            'sha256',
            $this->inviteEmail,
        );

        $ipKey = 'team-invite:ip:' . hash(
            'sha256',
            $ip,
        );

        if (
            RateLimiter::tooManyAttempts(
                $emailKey,
                self::INVITE_MAX_ATTEMPTS,
            )
            ||
            RateLimiter::tooManyAttempts(
                $ipKey,
                self::INVITE_MAX_ATTEMPTS,
            )
        ) {
            $seconds = max(
                RateLimiter::availableIn($emailKey),
                RateLimiter::availableIn($ipKey),
            );

            $minutes = max(
                1,
                (int) ceil($seconds / 60),
            );

            abort(
                429,
                "Too many invitation attempts. Please try again in {$minutes} minute(s).",
            );
        }
    }

    /**
     * Consume the invitation rate limits.
     */
    protected function hitRateLimit(): void
    {
        $ip = request()->ip() ?? 'unknown';

        RateLimiter::hit(
            'team-invite:email:' . hash(
                'sha256',
                $this->inviteEmail,
            ),
            self::INVITE_DECAY_SECONDS,
        );

        RateLimiter::hit(
            'team-invite:ip:' . hash(
                'sha256',
                $ip,
            ),
            self::INVITE_DECAY_SECONDS,
        );
    }
};

?>

<div>
    <flux:modal
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

            <div class="relative flex items-start gap-4">

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

                    <flux:heading
                        size="lg"
                        class="tracking-tight"
                    >
                        {{ __('Invite team member') }}
                    </flux:heading>

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

                @if($successMessage)
                    <flux:callout variant="success">
                    {{$successMessage}}
                    </flux:callout>
                @endif

                @if($errorMessage)
                    <flux:callout variant="danger">
                    {{$errorMessage}}
                    </flux:callout> 
                @endif

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

                    <p class="text-[11px] leading-5 text-white">
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
                    wire:loading.attr="disabled"
                    wire:target="invite"
                    class="group relative inline-flex h-10 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-purple-300/20 bg-linear-to-r from-purple-600 via-violet-500 to-fuchsia-500 px-5 text-xs font-semibold text-white shadow-lg shadow-purple-950/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-purple-900/30 focus:outline-none focus:ring-2 focus:ring-purple-400/40 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60"
                >

                    <span
                        class="absolute inset-0 bg-white/10 opacity-0 transition duration-300 group-hover:opacity-100"
                    ></span>

                    <span
                        wire:loading.remove
                        wire:target="invite"
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
                        wire:target="invite"
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

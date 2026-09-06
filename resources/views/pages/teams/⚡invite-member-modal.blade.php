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
        $this->authorize('inviteMember', $this->team);

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
        /*
         * Authorize using TeamPolicy::inviteMember().
         *
         * This checks TeamPermission::CreateInvitation rather
         * than requiring the user's pivot role to literally be
         * "admin".
         */
        $this->authorize('inviteMember', $this->team);

        $this->validate();

        $email = strtolower(trim($this->inviteEmail));

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

        $invitation = DB::transaction(function () use (
            $email,
            $temporaryPassword
        ): TeamInvitation {
            $inviter = auth()->user();

            abort_unless($inviter, 403);

            /**
             * Find an existing account by normalized email.
             */
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            /**
             * Create the account if it does not exist.
             */
            if (! $user) {
                $user = User::create([
                    'name' => Str::before($email, '@'),
                    'email' => $email,
                    'password' => Hash::make($temporaryPassword),
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
                    'password' => Hash::make($temporaryPassword),
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
             * Add the user to the team.
             */
           

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
        });

        /**
         * Queue the invitation email.
         *
         * The TeamInvitation notification implements ShouldQueue
         * and uses afterCommit(), so the email is processed by the
         * queue after the database transaction has committed.
         */
        Notification::route('mail', $email)->notify(
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

        $this->dispatch('team-member-invited');
    }
};
?>

<div>
    <flux:modal
        wire:model="show"
        name="invite-member"
        class="md:w-[500px]"
    >
        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    {{ __('Invite team member') }}
                </flux:heading>

                <flux:text class="mt-2">
                    {{ __('Invite someone to join :team.', [
                        'team' => $team->name,
                    ]) }}
                </flux:text>
            </div>

            <form wire:submit="invite" class="space-y-6">

                <flux:input
                    wire:model="inviteEmail"
                    :label="__('Email address')"
                    type="email"
                    placeholder="name@example.com"
                    autocomplete="email"
                    required
                />

                @error('inviteEmail')
                    <flux:text class="text-red-600">
                        {{ $message }}
                    </flux:text>
                @enderror

                <flux:select
                    wire:model="inviteRole"
                    :label="__('Role')"
                    required
                >
                    <flux:select.option value="member">
                        {{ __('Member') }}
                    </flux:select.option>

                    <flux:select.option value="admin">
                        {{ __('Admin') }}
                    </flux:select.option>
                </flux:select>

                @error('inviteRole')
                    <flux:text class="text-red-600">
                        {{ $message }}
                    </flux:text>
                @enderror

                <flux:text size="sm" class="text-zinc-500">
                    {{ __('The invited user will receive a temporary password by email and will be required to create a new password before continuing.') }}
                </flux:text>

                <div class="flex justify-end gap-3">

                    <flux:button
                        type="button"
                        variant="ghost"
                        wire:click="close"
                    >
                        {{ __('Cancel') }}
                    </flux:button>

                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            {{ __('Send invitation') }}
                        </span>

                        <span wire:loading>
                            {{ __('Sending...') }}
                        </span>
                    </flux:button>

                </div>
            </form>
        </div>
    </flux:modal>
</div>
<?php

namespace App\Notifications\Teams;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Do not serialize the entire User model.
     * The invitation ID and temporary password are all that are required.
     */
    public function __construct(
        public int $invitationId,
        public string $temporaryPassword,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invitation = TeamInvitationModel::query()
            ->with([
                'team',
                'inviter',
            ])
            ->findOrFail($this->invitationId);

        return (new MailMessage)
            ->subject(
                __("You've been invited to join :teamName", [
                    'teamName' => $invitation->team->name,
                ])
            )
            ->greeting(__('You have been invited!'))
            ->line(__(
                ':inviterName has invited you to join the :teamName team.',
                [
                    'inviterName' => $invitation->inviter->name,
                    'teamName' => $invitation->team->name,
                ]
            ))
            ->line(__('Your account has been created for you.'))
            ->line(__('Email: :email', [
                'email' => $invitation->email,
            ]))
            ->line(__('Temporary password: :password', [
                'password' => $this->temporaryPassword,
            ]))
            ->line(
                __('This temporary password is valid only until you create your new password.')
            )
            ->action(
                __('Sign In'),
                route('login', [
                    'invitation' => $invitation->code,
                ])
            )
            ->line(
                __('For security, you will be required to create a new password before accessing your account.')
            );
    }

    public function toArray(object $notifiable): array
    {
        $invitation = TeamInvitationModel::query()
            ->with('team')
            ->find($this->invitationId);

        if (! $invitation) {
            return [];
        }

        return [
            'invitation_id' => $invitation->id,
            'team_id' => $invitation->team_id,
            'team_name' => $invitation->team->name,
            'role' => $invitation->role->value,
        ];
    }
}
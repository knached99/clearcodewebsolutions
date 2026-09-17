<?php

namespace App\Notifications\Teams;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitation extends Notification
{
    public function __construct(
        public int $invitationId,
        public ?string $temporaryPassword = null,
    ) {}

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

        $mail = new MailMessage;

        $mail
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
            ));

        /*
         * New user:
         *
         * The invitation process created their account and gave
         * them a temporary password.
         */
        if ($this->temporaryPassword !== null) {
            $mail
                ->line(__('Your account has been created for you.'))
                ->line(__('Email: :email', [
                    'email' => $invitation->email,
                ]))
                ->line(__('Temporary password: :password', [
                    'password' => $this->temporaryPassword,
                ]))
                ->line(
                    __('For security, you will be required to create a new password before accessing your account.')
                );
        } else {
            /*
             * Existing user:
             *
             * Do not tell them to create a new password.
             */
            $mail->line(
                __('You already have an account. Sign in using your existing password.')
            );
        }

        $mail->action(
            __('Sign In'),
            route('login', [
                'invitation' => $invitation->code,
            ])
        );

        return $mail;
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

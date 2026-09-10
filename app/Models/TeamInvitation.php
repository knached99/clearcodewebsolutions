<?php

namespace App\Models;

use App\Enums\TeamRole;
use Database\Factories\TeamInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $code
 * @property int $team_id
 * @property string $email
 * @property TeamRole $role
 * @property int $invited_by
 * @property Carbon|null $expires_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team $team
 * @property-read User $inviter
 */
#[Fillable([
    'team_id',
    'email',
    'role',
    'invited_by',
    'expires_at',
    'accepted_at',
])]
class TeamInvitation extends Model
{
    /** @use HasFactory<TeamInvitationFactory> */
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamInvitation $invitation) {
            if (empty($invitation->code)) {
                $invitation->code = Str::random(64);
            }

            $invitation->email = strtolower(
                trim($invitation->email)
            );
        });

        static::updating(function (TeamInvitation $invitation) {
            if ($invitation->isDirty('email')) {
                $invitation->email = strtolower(
                    trim($invitation->email)
                );
            }
        });
    }

    /**
     * Get the team that owns this invitation.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Determine whether the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Determine whether the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Determine whether the invitation is still pending.
     */
    public function isPending(): bool
    {
        return ! $this->isAccepted()
            && ! $this->isExpired();
    }

    /**
     * Determine whether the invitation can still be accepted.
     */
    public function isValid(): bool
    {
        return $this->isPending();
    }

    /**
     * Mark the invitation as accepted.
     */
    public function accept(): void
    {
        $this->forceFill([
            'accepted_at' => now(),
        ])->save();
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}

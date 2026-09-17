<?php

namespace App\Models;

use App\Concerns\GeneratesUniqueTeamSlugs;
use App\Enums\TeamRole;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $is_personal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, TeamInvitation> $invitations
 * @property-read Collection<int, Membership> $memberships
 * @property-read Collection<int, User> $members
 * @property-read Collection<int, Ticket> $tickets
 * @property-read Collection<int, Work> $works
 */
#[Fillable([
    'name',
    'slug',
    'is_personal',
])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use GeneratesUniqueTeamSlugs;

    use HasFactory;
    use SoftDeletes;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Team $team) {
            if (empty($team->slug)) {
                $team->slug = static::generateUniqueTeamSlug(
                    $team->name
                );
            }
        });

        static::updating(function (Team $team) {
            if ($team->isDirty('name')) {
                $team->slug = static::generateUniqueTeamSlug(
                    $team->name,
                    $team->id
                );
            }
        });
    }

    /**
     * Get the team owner.
     */
    public function owner(): ?User
    {
        return $this->members()
            ->wherePivot('role', TeamRole::Owner->value)
            ->first();
    }

    /**
     * Get all members of this team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_members',
            'team_id',
            'user_id'
        )
            ->using(Membership::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get all memberships for this team.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all invitations for this team.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    /**
     * Get all tickets belonging to this team.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // gets all the projects belonging to this team
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    /**
     * Determine whether a user is an administrator.
     */
    public function isAdmin(User $user): bool
    {
        return $this->members()
            ->whereKey($user->id)
            ->wherePivot('role', TeamRole::Admin->value)
            ->exists();
    }

    /**
     * Determine whether a user is a member of this team.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()
            ->whereKey($user->id)
            ->exists();
    }

    /**
     * Get a user's role within this team.
     */
    public function roleFor(User $user): ?TeamRole
    {
        $membership = $this->memberships()
            ->where('user_id', $user->id)
            ->first();

        return $membership?->role;
    }

    /**
     * Determine whether the team has an administrator.
     */
    public function hasAdmin(): bool
    {
        return $this->memberships()
            ->where('role', TeamRole::Admin->value)
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

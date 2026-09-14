<?php

namespace App\Concerns;

use App\Data\TeamPermissions;
use App\Enums\TeamPermission;
use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin User
 */
trait HasTeams
{
    /**
     * Get the user's current team.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(
            Team::class,
            'current_team_id'
        );
    }

    /**
     * Get all teams the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
            'team_members',
            'user_id',
            'team_id'
        )
            ->using(Membership::class)
            ->withPivot([
                'role',
            ])
            ->withTimestamps();
    }

    /**
     * Get all team memberships.
     */
    public function teamMemberships()
    {
        return $this->hasMany(
            Membership::class,
            'user_id'
        );
    }

    /**
     * Get teams owned by the user.
     */
    public function ownedTeams(): BelongsToMany
    {
        return $this->teams()
            ->wherePivot(
                'role',
                TeamRole::Owner->value
            );
    }

    /**
     * Determine whether the user belongs to a team.
     */
    public function belongsToTeam(Team $team): bool
    {
        return $this->teams()
            ->whereKey($team->id)
            ->exists();
    }

    /**
     * Determine whether the user owns a team.
     */
    public function ownsTeam(Team $team): bool
    {
        return $this->teams()
            ->whereKey($team->id)
            ->wherePivot(
                'role',
                TeamRole::Owner->value
            )
            ->exists();
    }

    /**
     * Get the user's role on a team.
     */
    public function teamRole(Team $team): ?TeamRole
    {
        $membership = $this->teamMemberships()
            ->where('team_id', $team->id)
            ->first();

        return $membership?->role;
    }

    /**
     * Determine whether the user has a team permission.
     */
    public function hasTeamPermission(
        Team $team,
        TeamPermission $permission
    ): bool {
        $role = $this->teamRole($team);

        return $role?->hasPermission($permission) ?? false;
    }

    /**
     * Get the user's permissions for a team.
     */
    public function toTeamPermissions(
        Team $team
    ): TeamPermissions {
        $role = $this->teamRole($team);

        return new TeamPermissions(
            canUpdateTeam: $role?->hasPermission(
                TeamPermission::UpdateTeam
            ) ?? false,

            canDeleteTeam: $role?->hasPermission(
                TeamPermission::DeleteTeam
            ) ?? false,

            canAddMember: $role?->hasPermission(
                TeamPermission::AddMember
            ) ?? false,

            canUpdateMember: $role?->hasPermission(
                TeamPermission::UpdateMember
            ) ?? false,

            canRemoveMember: $role?->hasPermission(
                TeamPermission::RemoveMember
            ) ?? false,

            canCreateInvitation: $role?->hasPermission(
                TeamPermission::CreateInvitation
            ) ?? false,

            canCancelInvitation: $role?->hasPermission(
                TeamPermission::CancelInvitation
            ) ?? false,

            canViewProjects: $role?->hasPermission(
                TeamPermission::ViewProjects
            ) ?? false,

            canCreateProject: $role?->hasPermission(
                TeamPermission::CreateProject
            ) ?? false,

            canUpdateProject: $role?->hasPermission(
                TeamPermission::UpdateProject
            ) ?? false,

            canDeleteProject: $role?->hasPermission(
                TeamPermission::DeleteProject
            ) ?? false,
        );
    }

    /**
     * Determine whether the given team is the user's
     * currently selected team.
     */
    public function isCurrentTeam(Team $team): bool
    {
        return (int) $this->current_team_id === (int) $team->id;
    }

    /**
     * Switch the user's current team.
     *
     * Passing null clears the current team.
     */
    public function switchTeam(?Team $team): bool
    {
        if ($team === null) {
            $this->forceFill([
                'current_team_id' => null,
            ])->save();

            $this->unsetRelation('currentTeam');

            return true;
        }

        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        /*
         * Keep the in-memory relationship consistent.
         */
        $this->setRelation(
            'currentTeam',
            $team
        );

        return true;
    }
}

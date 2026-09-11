<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\User;
use App\Models\Work;

class WorkPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::ViewProjects,
            );
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Work $work): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::ViewProjects,
            );
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::CreateProject,
            );
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Work $work): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::UpdateProject,
            );
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Work $work): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::DeleteProject,
            );
    }
}

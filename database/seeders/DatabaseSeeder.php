<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Users
         */
        $khaled = User::query()->updateOrCreate(
            ['email' => 'khalednached11@gmail.com'],
            [
                'name' => 'Khaled Nached',
                'password' => Hash::make('UmrahTrip20259904??!!'),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );


        /*
         * Teams
         */
        $khaledTeam = Team::query()->updateOrCreate(
            ['name' => "Khaled Nached's Team"],
            [
                'is_personal' => false,
            ]
        );

        $supportTeam = Team::query()->updateOrCreate(
            ['name' => 'Support Team'],
            [
                'is_personal' => false,
            ]
        );

        /*
         * Memberships
         */
        $this->createMembership(
            team: $khaledTeam,
            user: $khaled,
            role: TeamRole::Owner,
        );

        /*
         * Set each user's current team.
         */
        $khaled->forceFill([
            'current_team_id' => $khaledTeam->id,
        ])->save();

    }

    private function createMembership(
        Team $team,
        User $user,
        TeamRole $role,
    ): Membership {
        return Membership::query()->updateOrCreate(
            [
                'team_id' => $team->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $role,
            ]
        );
    }
}
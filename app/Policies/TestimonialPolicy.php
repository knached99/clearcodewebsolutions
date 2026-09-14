<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Testimonials;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::ViewTestimonials
            );
    }

    public function view(User $user, Testimonials $testimonial): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::ViewTestimonials
            );
    }

    public function approve(User $user, Testimonials $testimonial): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::ApproveTestimonial
            );
    }

    public function deny(User $user, Testimonials $testimonial): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::DenyTestimonial
            );
    }

    public function delete(User $user, Testimonials $testimonial): bool
    {
        $team = $user->currentTeam;

        return $team !== null
            && $user->hasTeamPermission(
                $team,
                TeamPermission::DeleteTestimonial
            );
    }
}
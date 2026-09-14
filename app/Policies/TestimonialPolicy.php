<?php 

namespace App\Policies;

use App\Enums\TeamPermission; 
use App\Models\Testimonial; 
use App\Models\User; 

class TestimonialPolicy 
{
    // determines whether or not the user can view any testimonial
    public function viewAny(User $user): bool 
    {
        $team = $user->currentTeam; 

        return $team !== null && $user->hasTeamPermission($team, TeamPermission::ViewTestimonials);
    }

    // determines if they can view a specific testimonial 
    public function view(User $user, Testimonial $testimonial): bool 
    {
        $team = $user->currentTeam; 

        return $team !== null && $user->hasTeamPermission($team, TeamPermission::ViewTestimonials);
    }

    // determine if they can approve a testimonial 
    public function approve(User $user, Testimonial $testimonial): bool 
    {
        $team = $user->currentTeam; 
        
        return $team !== null && $user->hasTeamPermission($team, TeamPermission::ApproveTestimonial);
    }

    // determine if they can decline a testimonial 

    public function deny(User $user, Testimonial $testimonial): bool 
    {
        $team = $user->currentTeam; 
        
        return $team !== null && $user->hasTeamPermission($team, TeamPermission::DenyTestimonial);
    }

    // determine if they can delete a testimonial 

    public function delete(User $user, Testimonial $testimonial): bool 
    {
        $team = $user->currentTeam; 

        return $team !== null && $user->hasTeamPermission($team, TeamPermission::DeleteTestimonial);
    }


}
<?php

namespace App\Data;

readonly class TeamPermissions
{
    public function __construct(
        public bool $canUpdateTeam,
        public bool $canDeleteTeam,
        public bool $canAddMember,
        public bool $canUpdateMember,
        public bool $canRemoveMember,
        public bool $canCreateInvitation,
        public bool $canCancelInvitation,

        public bool $canViewProjects,
        public bool $canCreateProject,
        public bool $canUpdateProject,
        public bool $canDeleteProject,

        public bool $canViewTestimonials,
        public bool $canApproveTestimonial,
        public bool $canDenyTestimonial,
        public bool $canDeleteTestimonial,
    ) {
        //
    }
}
